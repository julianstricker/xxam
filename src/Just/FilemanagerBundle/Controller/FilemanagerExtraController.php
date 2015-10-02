<?php

namespace Just\FilemanagerBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use League\Flysystem\MountManager;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\NotSupportedException;
use League\Flysystem\RootViolationException;


class FilemanagerExtraController extends FilemanagerBaseController
{

    public function listfoldersAction(Request $request)
    {
        $filesystemid = $this->extractFilesystemIdFromPath($request->get('path', ''));
        $path = $this->extractPathFromPath($request->get('path', ''));
        $returndata = Array('success' => true, 'children' => Array());
        $filesystems = $this->getFilesystems();
        if (count($filesystems) == 0) {
            return $this->throwJsonError('No Filesystem found');
        } elseif ($filesystemid == false) {
            $returndata['children']=$this->getFilesystemsFolders($filesystems);
        } else {
            $filesystem = isset($filesystems[$filesystemid]) ? $filesystems[$filesystemid] : false;
            if (!$filesystem) {
                return $this->throwJsonError('Filesystem not found');
            }
            $returndata['children']=$this->getFoldersForPath($filesystem,$path);
        }
        $response = new Response(json_encode($returndata));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }

    public function listfilesAction(Request $request)
    {
        $filesystemid = $this->extractFilesystemIdFromPath($request->get('path', ''));
        $path = $this->extractPathFromPath($request->get('path', ''));
        $returndata = Array('success' => true, 'children' => Array());
        $filesystems = $this->getFilesystems();
        if (count($filesystems) == 0) {
            return $this->throwJsonError('No Filesystem found');
        } elseif ($filesystemid == false) {
            $returndata['children']=$this->getFilesystemsFolders($filesystems);
        } else {
            $filesystem = isset($filesystems[$filesystemid]) ? $filesystems[$filesystemid] : false;
            if (!$filesystem) {
                return $this->throwJsonError('Filesystem not found');
            }
            $returndata['children']=$this->getFilesForPath($filesystem,$path);
        }
        $response = new Response(json_encode($returndata));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }



    public function getThumbnailAction(Request $request)
    {
        $this->memcached = new \Memcached;
        $this->memcached->addServer('localhost', 11211);
        $tenant_id = $request->getSession()->get('tenant_id', 1);
        $originalpath = $request->get('path', '');
        $filesystemid = $this->extractFilesystemIdFromPath($request->get('path', ''));
        $path = $this->extractPathFromPath($request->get('path', ''));
        $cachename = 'JustFilemanagerBundleThumbnails' . $tenant_id . $request->get('size', 's') . md5($originalpath);
        //$this->memcached->delete($cachename);
        $filesystems = $this->getFilesystems();
        if (count($filesystems) == 0) {
            return $this->throwJsonError('No Filesystem found');
        } else {
            $filesystem = isset($filesystems[$filesystemid]) ? $filesystems[$filesystemid] : false;
            if (!$filesystem) {
                return $this->throwJsonError('Filesystem not found');
            }else {
                $response = $this->getThumbnailResponse($path,  $this->getFs($filesystem), $request->get('size', 's'), $cachename);
                return $response;
            }
        }

    }

    public function deletefileAction(Request $request)
    {
        $filesystemid = $this->extractFilesystemIdFromPath($request->get('path', ''));
        $path = $this->extractPathFromPath($request->get('path', ''));
        $filesystems = $this->getFilesystems();
        if (count($filesystems) == 0) {
            return $this->throwJsonError('No Filesystem found');
        } else {
            $filesystem = isset($filesystems[$filesystemid]) ? $filesystems[$filesystemid] : false;
            if (!$filesystem) {
                return $this->throwJsonError('Filesystem not found');
            }else {
                $fs = $this->getFs($filesystem);
                if ($fs->has($path)) {
                    $metadata = $fs->getMetadata($path);
                    if ($metadata['type'] == 'dir') {
                        $fs->deleteDir($path);
                    } else {
                        $fs->delete($path);
                    }
                    $returndata = Array('success' => true, 'metadata' => $metadata);
                    $response = new Response(json_encode($returndata));
                    $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
                    return $response;
                }else{
                    return $this->throwJsonError('File not found');
                }

            }
        }

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
                //$metadata = $fs->getMetadata($path);
                if ($filesystemid == $filesystemid2) {
                    try {
                        $fs->rename($path, $newname);
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
                $returndata = Array('success' => true);
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
                //$metadata = $fs->getMetadata($path);
                if ($filesystemid == $filesystemid2) {
                    try {
                        $fs->copy($path, $newname);
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
                $returndata = Array('success' => true);
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
            $returndata = Array('success' => true);

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
