<?php

namespace Xxam\FilemanagerBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use League\Flysystem\MountManager;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\NotSupportedException;
use League\Flysystem\RootViolationException;


class FilemanagerExtraController extends FilemanagerBaseController
{

    /**
     * @param string $path
     * @param integer|boolean $filesystemid
     * @param bool $getfolders
     * @return Response
     * @internal param $filesystems
     */
    private function listfolders($path,$filesystemid,$getfolders=true){
        $returndata = Array('success' => true, 'children' => Array());
        $filesystems = $this->getFilesystems();
        if (count($filesystems) == 0) {
            return $this->throwJsonError('No Filesystem found');
        } elseif ($filesystemid == false) {
            $returndata['children']=$this->getFilesystemsFolders($filesystems);
        } else {
            $filesystem = isset($filesystems[$filesystemid]) ? $filesystems[$filesystemid] : null;
            if (!$filesystem) {
                return $this->throwJsonError('Filesystem not found');
            }
            $returndata['children']=$getfolders ? $this->getFoldersForPath($filesystem,$path) : $this->getFilesForPath($filesystem,$path);;
        }
        $response = new Response(json_encode($returndata));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;

    }

    public function listfoldersAction(Request $request)
    {
        $filesystemid = $this->extractFilesystemIdFromPath($request->get('path', ''));
        $path = $this->extractPathFromPath($request->get('path', ''));
        return $this->listfolders($path,$filesystemid, true);
    }

    public function listfilesAction(Request $request)
    {
        $filesystemid = $this->extractFilesystemIdFromPath($request->get('path', ''));
        $path = $this->extractPathFromPath($request->get('path', ''));
        return $this->listfolders($path,$filesystemid, false);
    }



    public function getThumbnailAction(Request $request)
    {
        $this->memcached = $this->get('memcached');
        $tenant_id = $request->getSession()->get('tenant_id', 1);
        $originalpath = $request->get('path', '');
        $filesystemid = $this->extractFilesystemIdFromPath($request->get('path', ''));
        $path = $this->extractPathFromPath($request->get('path', ''));
        $cachename = 'XxamFilemanagerBundleThumbnails' . $tenant_id . $request->get('size', 's') . md5($originalpath);
        //$this->memcached->delete($cachename);
        $filesystems = $this->getFilesystems();
        if (count($filesystems) == 0) {
            return $this->throwJsonError('No Filesystem found');
        } else {
            $filesystem = isset($filesystems[$filesystemid]) ? $filesystems[$filesystemid] : null;
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
            $filesystem = isset($filesystems[$filesystemid]) ? $filesystems[$filesystemid] : null;
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

    private function copyormovefile($source,$destination,$domove=true){
        $filesystemid = $this->extractFilesystemIdFromPath($source);
        $path = $this->extractPathFromPath($source);
        $filesystemid2 = $this->extractFilesystemIdFromPath($destination);
        $newname = $this->extractPathFromPath($destination);
        $returndata = Array('success' => false);
        $filesystems = $this->getFilesystems();
        if (count($filesystems) == 0) {
            return $this->throwJsonError('No Filesystem found');
        } else {
            $filesystem = isset($filesystems[$filesystemid]) ? $filesystems[$filesystemid] : null;
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
                    $filesystem2 = isset($filesystems[$filesystemid2]) ? $filesystems[$filesystemid2] : null;
                    if (!$filesystem2) {
                        return $this->throwJsonError('Filesystem not found');
                    }
                    $fs2 = $this->getFs($filesystem2);

                    $manager = new MountManager([
                        'fssource' => $fs,
                        'fsdestination' => $fs2,
                    ]);
                    try {
                        if ($domove) {
                            $manager->move('fssource:/' . $path, 'fsdestination:/' . $newname);
                        }else{
                            $manager->copy('fssource:/' . $path, 'fsdestination:/' . $newname);
                        }
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

    public function movefileAction(Request $request)
    {
        $response=$this->copyormovefile($request->get('path', ''),$request->get('newname', ''),true);
        return $response;
    }

    public function copyfileAction(Request $request)
    {
        $response=$this->copyormovefile($request->get('path', ''),$request->get('newname', ''),true);
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
            $filesystem = isset($filesystems[$filesystemid]) ? $filesystems[$filesystemid] : null;
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
            $filesystem = isset($filesystems[$filesystemid]) ? $filesystems[$filesystemid] : null;
            if (!$filesystem) {
                return $this->throwJsonError('Filesystem not found');
            }
            $fs = $this->getFs($filesystem);
            if ($fs->has($path)) {
                $metadata = $fs->getMetadata($path);

                $content = $fs->read($path);
                $response = new Response($content);
                if (!empty($metadata['mimetype'])) $response->headers->set('Content-Type', $metadata['mimetype']);
                if (!empty($metadata['size'])) $response->headers->set('Content-Length', $metadata['size']);
                if (!empty($metadata['basename'])) $response->headers->set('Content-disposition', 'inline; filename="' . $metadata['basename'] . '"');
                return $response;
            }
        }
        $response = new Response(json_encode($returndata));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }

}
