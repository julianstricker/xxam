<?php

namespace Just\FilemanagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Dropbox\DropboxAdapter;
use Dropbox\Client as DropboxClient;
use League\Flysystem\Adapter\Ftp as FtpAdapter;
use League\Flysystem\Sftp\SftpAdapter;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\Cached\Storage\Memcached as Cache;
use League\Flysystem\Plugin\ListWith;
use League\Flysystem\Plugin\ListPaths;
use League\Flysystem\MountManager;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\NotSupportedException;
use League\Flysystem\RootViolationException;

use Just\FilemanagerBundle\Helper\FlysystemPlugins\ThumbnailDropbox;

class FilemanagerExtraController extends FilemanagerBaseController
{



    public function listfoldersAction(Request $request)
    {
         $path = $request->get('path', '');
        $filesystemid = $this->extractFilesystemIdFromPath($request->get('path', ''));
        $path = $this->extractPathFromPath($request->get('path', ''));

        $returndata = Array('success' => true, 'children' => Array());
        $filesystems = $this->getFilesystems();
        if (count($filesystems) == 0) {
            return $this->throwJsonError('No Filesystem found');
        } elseif ($filesystemid == false) {
            foreach ($filesystems as $filesystem) {
                $returndata['children'][] = Array(
                    'id' => (string)$filesystem->getId(),
                    'basename' => (string)$filesystem->getId(),
                    'name' => $filesystem->getFilesystemname(),
                    'icon' => $filesystem->getUser() ? '/bundles/justadmin/icons/16x16/folder_user.png' : '/bundles/justadmin/icons/16x16/folder.png',
                    'leaf' => false
                );
            }
        } else {
            $filesystem = isset($filesystems[$filesystemid]) ? $filesystems[$filesystemid] : false;
            if (!$filesystem) {
                return $this->throwJsonError('Filesystem not found');
            }
            $fs = $this->getFs($filesystem);
            $contents = $fs->listContents($path);
            //dump($contents);
            foreach ($contents as $content) {
                if ($content['type'] == 'dir') {
                    $returndata['children'][] = Array(
                        'id' => (string)$filesystem->getId() . '/' . $content['path'],
                        'basename' => $content['basename'],
                        'name' => $content['basename'],
                        'icon' => '/bundles/justadmin/icons/16x16/folder.png',
                        'timestamp' => $content['timestamp']
                    );
                }
            }

        }


        $response = new Response(json_encode($returndata));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }

    public function listfilesAction(Request $request)
    {
        $refresh = $request->get('refresh', false);
        $filesystemid = $this->extractFilesystemIdFromPath($request->get('path', ''));
        $path = $this->extractPathFromPath($request->get('path', ''));
        $returndata = Array('success' => true, 'children' => Array());
        $filesystems = $this->getFilesystems();
        if (count($filesystems) == 0) {
            return $this->throwJsonError('No Filesystem found');
        } elseif ($filesystemid == false) {
            foreach ($filesystems as $filesystem) {

                $returndata['children'][] = Array(
                    'id' => (string)$filesystem->getId(),
                    'name' => $filesystem->getFilesystemname(),
                    'timestamp' => null,
                    'type' => $filesystem->getUser() ? 'privatefs' : 'fs',
                    'size' => null
                );
            }
        } else {
            $filesystem = isset($filesystems[$filesystemid]) ? $filesystems[$filesystemid] : false;
            if (!$filesystem) {
                return $this->throwJsonError('Filesystem not found');
            }
            $fs = $this->getFs($filesystem);
            //if ($refresh) $fs->getAdapter()->flush();
            $contents = $fs->listContents($path);
            //$contents = $fs->listWith(['mimetype', 'size', 'timestamp'], $path, true);

            //dump($contents);
            foreach ($contents as $content) {

                $returndata['children'][] = Array(
                    'id' => (string)$filesystem->getId() . '/' . $content['path'],
                    'name' => $content['basename'],
                    'timestamp' => $content['timestamp'],
                    'type' => $content['type'],
                    'size' => $content['size'],
                    //'thumbnail'=>$fs->getThumbnail($content['path'],'s')
                );

            }

        }


        $response = new Response(json_encode($returndata));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }

    public function getThumbnailAction(Request $request)
    {
        $this->memcached = new \Memcached;
        $this->memcached->addServer('localhost', 11211);
        $fileextensionswiththumbnails = $this->container->getParameter('fileextensionswiththumbnails');
        $tenant_id = $request->getSession()->get('tenant_id', 1);
        $originalpath = $request->get('path', '');
        $filesystemid = $this->extractFilesystemIdFromPath($request->get('path', ''));
        $path = $this->extractPathFromPath($request->get('path', ''));
        $size = $request->get('size', 's');
        $ctime = new \DateTime();
        $cachename = 'JustFilemanagerBundleThumbnails' . $tenant_id . $size . md5($originalpath);
        //$this->memcached->delete($cachename);
        $filesystems = $this->getFilesystems();
        if (count($filesystems) == 0) {
            return $this->throwJsonError('No Filesystem found');
        } else {
            $filesystem = isset($filesystems[$filesystemid]) ? $filesystems[$filesystemid] : false;
            if (!$filesystem) {
                return $this->throwJsonError('Filesystem not found');
            }
            $fs = $this->getFs($filesystem);
            $pathexpl = explode('.', $path);
            $fileextension = strtolower($pathexpl[count($pathexpl) - 1]);
            if ($size == 'xxs' || !in_array($fileextension, $fileextensionswiththumbnails)) {
                $response = new Response($this->getIcon($path, $size));
            } else {
                $timestamp = $fs->getTimestamp($path);
                $fromcache = $this->getImageFromCache($cachename, $timestamp);
                if ($fromcache) { //ist bereits im cache:
                    $thumbnaildata = $fromcache;
                    $ctime = new \DateTime();
                    $ctime->setTimestamp(intval($thumbnaildata[0]));
                    $response = new Response($thumbnaildata[1]);
                } else {
                    $thumbnaildata = $fs->getThumbnail($path, $size);
                    if ($thumbnaildata[1]) {
                        $thumbnaildata[0] = $ctime->getTimestamp();
                        $this->memcached->set($cachename, serialize($ctime->getTimestamp() . '|' . $thumbnaildata[1]));
                        $response = new Response($thumbnaildata[1]);
                    } else { //icon ausgeben:
                        $response = new Response($this->getIcon($path, $size));


                    }
                    //echo 'new';

                    //dump($thumbnaildata);
                }
            }
        }


        $expires = 1 * 24 * 60 * 60;

        $response->headers->set('Content-Length', strlen($thumbnaildata[1]));
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Last-Modified', $ctime->format('D, d M Y H:i:s') . ' GMT');
        $response->headers->set('Cache-Control', 'maxage=' . $expires);
        $response->headers->set('Content-Type', 'image/png; charset=UTF-8');
        $response->headers->set('Expires', gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
        return $response;
    }

    public function deletefileAction(Request $request)
    {
        $filesystemid = $this->extractFilesystemIdFromPath($request->get('path', ''));
        $path = $this->extractPathFromPath($request->get('path', ''));
        $returndata = Array('success' => false);
        $filesystems = $this->getFilesystems();
        if (count($filesystems) == 0) {
            return $this->throwJsonError('No Filesystem found');
        } else {
            $filesystem = isset($filesystems[$filesystemid]) ? $filesystems[$filesystemid] : false;
            if (!$filesystem) {
                return $this->throwJsonError('Filesystem not found');
            }
            $fs = $this->getFs($filesystem);
            if ($fs->has($path)) {
                $metadata = $fs->getMetadata($path);
                if ($metadata['type'] == 'dir') {
                    $fs->deleteDir($path);
                } else {
                    $fs->delete($path);
                }
                $returndata = Array('success' => true, 'metadata' => $metadata);
            }
        }
        $response = new Response(json_encode($returndata));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }

    public function movefileAction(Request $request)
    {
        $filesystemid = $this->extractFilesystemIdFromPath($request->get('path', ''));
        $path = $this->extractPathFromPath($request->get('path', ''));
        $filesystemid2 = $this->extractFilesystemIdFromPath($request->get('newname', ''));
        $newname = $this->extractPathFromPath($request->get('newname', ''));
        $returndata = Array('success' => false);
        $filesystems = $this->getFilesystems();
        if (count($filesystems) == 0) {
            return $this->throwJsonError('No Filesystem found');
        } else {
            $filesystem = isset($filesystems[$filesystemid]) ? $filesystems[$filesystemid] : false;
            if (!$filesystem) {
                return $this->throwJsonError('Filesystem not found');
            }
            $fs = $this->getFs($filesystem);
            if ($fs->has($path)) {
                $metadata = $fs->getMetadata($path);
                if ($filesystemid == $filesystemid2) {
                    try {
                        $resp = $fs->rename($path, $newname);
                    } catch (FileExistsException $e) {
                        return $this->throwJsonError($e->getMessage());
                    } catch (FileNotFoundException $e) {
                        return $this->throwJsonError($e->getMessage());
                    } catch (NotSupportedException $e) {
                        return $this->throwJsonError($e->getMessage());
                    } catch (RootViolationException $e) {
                        return $this->throwJsonError($e->getMessage());
                    } catch (\Exception $e) {
                        return $this->throwJsonError($e->getMessage());
                    }
                } else {
                    $filesystem2 = isset($filesystems[$filesystemid2]) ? $filesystems[$filesystemid2] : false;
                    if (!$filesystem2) {
                        return $this->throwJsonError('Filesystem not found');
                    }
                    $fs2 = $this->getFs($filesystem2);

                    $manager = new MountManager([
                        'fssource' => $fs,
                        'fsdestination' => $fs2,
                    ]);
                    try {
                        $manager->move('fssource:/' . $path, 'fsdestination:/' . $newname);
                    } catch (FileExistsException $e) {
                        return $this->throwJsonError($e->getMessage());
                    } catch (FileNotFoundException $e) {
                        return $this->throwJsonError($e->getMessage());
                    } catch (NotSupportedException $e) {
                        return $this->throwJsonError($e->getMessage());
                    } catch (RootViolationException $e) {
                        return $this->throwJsonError($e->getMessage());
                    } catch (\Exception $e) {
                        return $this->throwJsonError($e->getMessage());
                    }
                }
                $returndata = Array('success' => true, 'resp' => $resp);
            }
        }
        $response = new Response(json_encode($returndata));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }

    public function copyfileAction(Request $request)
    {
        $filesystemid = $this->extractFilesystemIdFromPath($request->get('path', ''));
        $path = $this->extractPathFromPath($request->get('path', ''));
        $filesystemid2 = $this->extractFilesystemIdFromPath($request->get('newname', ''));
        $newname = $this->extractPathFromPath($request->get('newname', ''));
        $returndata = Array('success' => false);
        $filesystems = $this->getFilesystems();
        if (count($filesystems) == 0) {
            return $this->throwJsonError('No Filesystem found');
        } else {
            $filesystem = isset($filesystems[$filesystemid]) ? $filesystems[$filesystemid] : false;
            if (!$filesystem) {
                return $this->throwJsonError('Filesystem not found');
            }
            $fs = $this->getFs($filesystem);
            if ($fs->has($path)) {
                $metadata = $fs->getMetadata($path);
                if ($filesystemid == $filesystemid2) {
                    try {
                        $resp = $fs->copy($path, $newname);
                    } catch (FileExistsException $e) {
                        return $this->throwJsonError($e->getMessage());
                    } catch (FileNotFoundException $e) {
                        return $this->throwJsonError($e->getMessage());
                    } catch (NotSupportedException $e) {
                        return $this->throwJsonError($e->getMessage());
                    } catch (RootViolationException $e) {
                        return $this->throwJsonError($e->getMessage());
                    } catch (\Exception $e) {
                        return $this->throwJsonError($e->getMessage());
                    }
                } else {
                    $filesystem2 = isset($filesystems[$filesystemid2]) ? $filesystems[$filesystemid2] : false;
                    if (!$filesystem2) {
                        return $this->throwJsonError('Filesystem not found');
                    }
                    $fs2 = $this->getFs($filesystem2);

                    $manager = new MountManager([
                        'fssource' => $fs,
                        'fsdestination' => $fs2,
                    ]);
                    try {
                        $manager->copy('fssource:/' . $path, 'fsdestination:/' . $newname);
                    } catch (FileExistsException $e) {
                        return $this->throwJsonError($e->getMessage());
                    } catch (FileNotFoundException $e) {
                        return $this->throwJsonError($e->getMessage());
                    } catch (NotSupportedException $e) {
                        return $this->throwJsonError($e->getMessage());
                    } catch (RootViolationException $e) {
                        return $this->throwJsonError($e->getMessage());
                    } catch (\Exception $e) {
                        return $this->throwJsonError($e->getMessage());
                    }
                }
                $returndata = Array('success' => true, 'resp' => $resp);
            }
        }
        $response = new Response(json_encode($returndata));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }

    public function writefileAction(Request $request)
    {

        $content = $request->get('content', '');
        $filesystemid = $this->extractFilesystemIdFromPath($request->get('path', ''));
        $path = $this->extractPathFromPath($request->get('path', ''));
        $returndata = Array('success' => false);
        $filesystems = $this->getFilesystems();
        if (count($filesystems) == 0) {
            return $this->throwJsonError('No Filesystem found');
        } else {
            $filesystem = isset($filesystems[$filesystemid]) ? $filesystems[$filesystemid] : false;
            if (!$filesystem) {
                return $this->throwJsonError('Filesystem not found');
            }
            $fs = $this->getFs($filesystem);
            $fs->put($path, $content);
            $returndata = Array('success' => true, 'metadata' => $metadata);

        }
        $response = new Response(json_encode($returndata));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }

    public function readfileAction(Request $request)
    {
        $filesystemid = $this->extractFilesystemIdFromPath($request->get('path', ''));
        $path = $this->extractPathFromPath($request->get('path', ''));
        $returndata = Array('success' => false);
        $filesystems = $this->getFilesystems();
        if (count($filesystems) == 0) {
            return $this->throwJsonError('No Filesystem found');
        } else {
            $filesystem = isset($filesystems[$filesystemid]) ? $filesystems[$filesystemid] : false;
            if (!$filesystem) {
                return $this->throwJsonError('Filesystem not found');
            }
            $fs = $this->getFs($filesystem);
            if ($fs->has($path)) {
                $metadata = $fs->getMetadata($path);
                $content = $fs->read($path);
                $response = new Response($content);
                if ($metadata['mimetype']) $response->headers->set('Content-Type', $metadata['mimetype']);
                if ($metadata['size']) $response->headers->set('Content-Length', $metadata['size']);
                $response->headers->set('Content-disposition', 'inline; filename="' . $metadata['basename'] . '"');
                return $response;
            }
        }
        $response = new Response(json_encode($returndata));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }

}
