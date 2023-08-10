<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\{
    Http,
    Storage,
    DB
};
use Illuminate\Http\Response;
use App\Models\{
    User,
    Log,
    Metadata,
    Image
};
use Intervention\Image\Facades\Image as ImageInt;

trait GlobalTrait
{
    public function cypher($request, $type)
    {
        $key = hex2bin('43e3b0f3405b2b7707e398f0171a91a2');
        $iv =  hex2bin('91bc845cbd4076fb9a0fdc2ad37e425d');
        $cypherMethod = 'AES-128-CBC';

        if ($type == 'decrypt') {
            $encrypted = $request->dt;
            $decrypted = openssl_decrypt($encrypted, $cypherMethod, $key, OPENSSL_ZERO_PADDING, $iv);

            $decrypted = trim($decrypted);
            $data = json_decode($decrypted);
        } else {
            $data = openssl_encrypt(json_encode($request), $cypherMethod, $key, 0, $iv);
        }

        return $data;
    }

    public function populateInclusions($payload)
    {
        foreach ($payload as $value) {
            if ($value->detail) {
                $value->detail->inclusions = ($value->detail->inclusions) ? Inclusion::select('id', 'name')
                    ->whereIn('id', json_decode($value->detail->inclusions))
                    ->get() : [];
            }
        }
    }

    /**
     * GlobalTrait getPage
     * @param  string $slug
     * @return object/array
     */
    public function getPage($slug)
    {
        $page = Page::select('id', 'title', 'subtitle', 'description')
            ->with('metadata')
            ->where('slug', $slug)
            ->first();

        return $page;
    }

    public function generateLog($user, $message)
    {
        $authenticated = $this->getAuthenticatedUser($user);

        Log::create([
            'message' => "{$authenticated->detail->full_name} ({$authenticated->role->name}) {$message}"
        ]);
    }

    public function generateSystemLog($user, $message, $log_to, $pivot = null)
    {
        $authenticated = $this->getAuthenticatedUser($user);

        SystemLog::create([
            'user_id' => $authenticated->id,
            'message' => "{$authenticated->detail->full_name} {$message}",
            'pivot_id' => $pivot ? $pivot->id : null,
            'log_to' => $log_to,
        ]);
    }

    public function getAuthenticatedUser($user)
    {
        if ($user) {
            $user = User::where('id', $user->id)
                ->with([
                    'role' => function ($query) {
                        $query->select('id', 'identifier', 'permissions', 'name', 'type');
                    }
                ])
                ->first();

            switch ($user->role->type) {
                case 'customer':
                    $user->load([
                        'addresses.country',
                        'userCustomerDetail' => function ($query) {
                            $query->with(['specialist.userSpecialistDetail']);
                        },
                        'images',
                    ]);
                    $user->detail = $user->userCustomerDetail;
                    $user->detail->preferred_brands = ($user->detail->preferred_brands) ? Brand::select('id', 'name')
                        ->whereIn('id', json_decode($user->detail->preferred_brands))
                        ->get() : [];

                    $user->detail->interests = ($user->detail->interests) ? Interest::select('id', 'name')
                        ->whereIn('id', json_decode($user->detail->interests))
                        ->get() : [];
                    unset($user->userCustomerDetail);

                    break;
                case 'specialist':
                    $user->load([
                        'userSpecialistDetail' => function ($query) {
                            $query->select('id', 'user_id', 'first_name', 'last_name', 'full_name', 'slug', 'contact_number');
                        },
                    ]);
                    $user->detail = $user->userSpecialistDetail;
                    break;
                default:
                    $user->load([
                        'userDetail' => function ($query) {
                            $query->select('id', 'user_id', 'first_name', 'last_name', 'full_name', 'slug', 'contact_number');
                        },
                    ]);
                    $user->detail = $user->userDetail;
                    break;
            }
        }
        return $user;
    }

    public function metatags($record, $request)
    {
        $metadata = Metadata::where('parent_id', $record->id)->first();
        if (!$metadata) {
            Metadata::create([
                'parent_id' => $record->id,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'link_rel' => $request->link_rel
            ]);
        } else {
            $metadata->update([
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'link_rel' => $request->link_rel
            ]);
        }
    }

    public function recordExist($record): Response
    {
        if ($record) {
            return response([
                'record' => $record
            ]);
        } else {
            return response([
                'errors' => [
                    'Not Found.'
                ]
            ]);
        }
    }

    public function slugify($str, $model, $ref_id = null)
    {
        $MODEL = '\App\Models\\' . $model;
        $slug = str_slug(strtolower($str), '-');

        $record =  $MODEL::whereSlug($slug)->first();
        if (!is_null($record)) {
            $query  = $MODEL::where('slug', 'like', $slug . '%')->whereNull('deleted_at');

            if (!is_null($ref_id)) {
                $query = $query->where('id', '!=', $ref_id);
            }

            $count  = $query->latest('id')->count();
            if ($count > 0) {
                $slug = "{$slug}-{$count}";
            }
        }

        return $slug;
    }

    /**
     * [addImages function]
     * @param [string] $model - Name of the model in snake case. Make it singular (e.g. product_variant, customer_detail)
     * @param [object/array] $r - The Request object. It contains the images
     * @param [Table record] $record - Model record
     * @param [String] $field  - string, name of the file field
     */
    function addImages($model, $r, $record, $field = NULL)
    {
        $file = ($field) ?? 'file';
        $existingImagesCount = $record->images($model)->count();

        $file_id  = $file . '_id';
        $file_title  = $file . '_title';
        $file_alt = $file . '_alt';
        $file_category = $file . '_category';
        $file_sequence = $file . '_sequence';
        $file_caption = $file . '_caption';
        $file_link = $file . '_link';

        foreach ($r->$file as $key => $image) {
            $proceed = true;

            if ($proceed) {
                $uploadedImage = $this->uploadFile($image, null, null, $model);
                $imageData = [
                    'model' => $model,
                    'title' => (isset($r->$file_title)) ? $r->$file_title[$key] : null,
                    'alt' => (isset($r->$file_alt)) ? $r->$file_alt[$key] : null,
                    'path' => $uploadedImage->path,
                    'path_resized' => $uploadedImage->path_resized,
                    'category' => (isset($r->$file_category)) ? $r->$file_category[$key] : $field,
                    'sequence' => (isset($r->$file_sequence)) ? $r->$file_sequence[$key] : 0, //$existingImagesCount + 1,
                    'name' => $uploadedImage->original_file_name,
                    'size' => $uploadedImage->file_size,
                    'caption'   => (isset($r->$file_caption)) ? $r->$file_caption[$key] : null,
                    'link'   => (isset($r->$file_link)) ? $r->$file_link[$key] : null,
                ];
                $record->uploadImage($imageData);
            }
        }
    }

    function updateImages($model, $r, $record, $field = NULL)
    {
        $file = ($field) ?? 'file';
        $existingImagesCount = $record->images($model)->count();

        $file_id  = $file . '_id';
        $file_title  = $file . '_title';
        $file_alt = $file . '_alt';
        $file_category = $file . '_category';
        $file_sequence = $file . '_sequence';
        $file_caption = $file . '_caption';
        $file_link = $file . '_link';

        if ($r->$file_id) {
            // $file = ($field) ?? 'file';
            foreach ($r->$file_id as $key => $image_id) {
                if (!$image_id) { # if new image, upload this
                    $image = $r->$file[$key];
                    $uploadedImage = $this->uploadFile($image, null, null, $model);
                    $imageData = [
                        'title' => (isset($r->$file_title)) ? $r->$file_title[$key] : null,
                        'alt' => (isset($r->$file_alt)) ? $r->$file_alt[$key] : null,
                        'sequence' => (isset($r->$file_sequence)) ? $r->$file_sequence[$key] : 0, //$existingImagesCount + 1,
                        'path' => $uploadedImage->path,
                        'path_resized' => $uploadedImage->path_resized,
                        'category' => (isset($r->$file_category)) ? $r->$file_category[$key] : null,
                        'model' => $model,
                        'name' => $uploadedImage->original_file_name,
                        'size' => $uploadedImage->file_size,
                        'caption'   => (isset($r->$file_caption)) ? $r->$file_caption[$key] : null,
                        'link'   => (isset($r->$file_link)) ? $r->$file_link[$key] : null
                    ];
                    $record->uploadImage($imageData);
                } else { # if old image
                    $_IMAGE_Model = '\App\Models\\Image';
                    if (isset($r->$file[$key])) { # if a new image is selected
                        # update the old image data
                        $existingImage = $_IMAGE_Model::where('id', $image_id)->first();
                        $uploadedImage = $this->uploadFile($r->$file[$key], $existingImage->path, $existingImage->path_resized, $model);
                        $existingImage->update([
                            'title' => (isset($r->$file_title)) ? $r->$file_title[$key] : null,
                            'alt' => (isset($r->$file_alt)) ? $r->$file_alt[$key] : null,
                            'sequence' => (isset($r->$file_sequence)) ? $r->$file_sequence[$key] : 0, //$existingImagesCount + 1,
                            'path' => $uploadedImage->path,
                            'path_resized' => $uploadedImage->path_resized,
                            'name' => $uploadedImage->original_file_name,
                            'size' => $uploadedImage->file_size,
                            'caption'   => (isset($r->$file_caption)) ? $r->$file_caption[$key] : null,
                            'link'   => (isset($r->$file_link)) ? $r->$file_link[$key] : null
                        ]);
                    } else { # if no new image is selected
                        $existingImage = $_IMAGE_Model::where('id', $image_id)->first();
                        $existingImage->update([
                            'title' => (isset($r->$file_title)) ? $r->$file_title[$key] : null,
                            'alt' => (isset($r->$file_alt)) ? $r->$file_alt[$key] : null,
                            'sequence' => (isset($r->$file_sequence)) ? $r->$file_sequence[$key] : 0, //$existingImagesCount + 1,
                            'caption'   => (isset($r->$file_caption)) ? $r->$file_caption[$key] : null,
                            'link'   => (isset($r->$file_link)) ? $r->$file_link[$key] : null
                        ]);
                    }
                }
            }
        }
    }

    /**
     * [imageUploader function]
     * @param [string] $model - Name of the model in snake case. Make it singular (e.g. product_variant, customer_detail)
     * @param [object/array] $r - The Request object. It contains the images
     * @param [Table record] $record - Model record
     * @param [String] $field  - string, name of the file field
     * @param [String] $action - add / update
     */
    function imageUploader($model, $r, $record, $field = NULL, $action)
    {
        $imageField          = ($field) ?? 'image';
        $existingImagesCount = $record->images($model)->count();

        $imageFieldId        = $imageField . '_id';
        $imageFieldIdOld     = $imageField . '_id_old';
        $imageFieldTitle     = $imageField . '_title';
        $imageFieldAlt       = $imageField . '_alt';
        $imageFieldCategory  = $imageField . '_category';
        $imageFieldSequence  = $imageField . '_sequence';
        $imageFieldCaption   = $imageField . '_caption';
        $imageFieldExisting  = $imageField . '_existing';
        $imageFieldMultiple  = $imageField . '_upload_multiple';

        switch ($action) {
            case 'add':
                foreach ($r->$imageFieldId as $key => $id) {
                    $uploadedImage = null;

                    if (!$r->$imageFieldId[$key] && $r->$imageFieldExisting[$key]) {
                        $uploadedImage = Image::where('id', $r->$imageFieldExisting[$key])->first();
                    } elseif (!$r->$imageFieldId[$key] && !$r->$imageFieldExisting[$key]) {
                        $uploadedImage = $this->uploadFile($r->$imageField[$key], null, null, $model);
                    }

                    if ($uploadedImage) {
                        $payload = [
                            'model'        => $model,
                            'title'        => (isset($r->$imageFieldTitle)) ? $r->$imageFieldTitle[$key] : null,
                            'alt'          => (isset($r->$imageFieldAlt)) ? $r->$imageFieldAlt[$key] : null,
                            'path'         => (isset($r->$imageField[$key])) ? $uploadedImage->path : $uploadedImage->original_path,
                            'path_resized' => (isset($r->$imageField[$key])) ? $uploadedImage->path_resized : $uploadedImage->original_path_resized,
                            'category'     => (isset($r->$imageFieldCategory)) ? $r->$imageFieldCategory[$key] : $field,
                            'sequence'     => (isset($r->$imageFieldSequence)) ? $r->$imageFieldSequence[$key] : 0,
                            'name'         => $uploadedImage->original_file_name,
                            'size'         => $uploadedImage->file_size,
                            'caption'      => (isset($r->$imageFieldCaption)) ? $r->$imageFieldCaption[$key] : null
                        ];

                        $record->uploadImage($payload);
                    }
                }
                break;
            case 'update':
                if (isset($r->$imageFieldId)) {
                    foreach ($r->$imageFieldId as $key => $id) {
                        $uploadedImage = null;
                        $new = false;

                        if (!$r->$imageFieldId[$key] && $r->$imageFieldExisting[$key]) {
                            $uploadedImage = Image::where('id', $r->$imageFieldExisting[$key])->first();
                            $new = true;
                        } elseif ($r->$imageFieldId[$key] && $r->$imageFieldExisting[$key]) {
                            $uploadedImage = Image::where('id', $r->$imageFieldExisting[$key])->first();
                        } elseif (!$r->$imageFieldId[$key] && !$r->$imageFieldExisting[$key]) {
                            if (isset($r->$imageField[$key])) {
                                $uploadedImage = $this->uploadFile($r->$imageField[$key], null, null, $model);
                                $new = true;
                            }
                        }

                        if ($uploadedImage && isset($r->$imageFieldMultiple[$key])) {
                            if (!!$r->$imageFieldMultiple[$key]) {
                                if ($new) {
                                    $payload = [
                                        'model'        => $model,
                                        'title'        => (isset($r->$imageFieldTitle)) ? $r->$imageFieldTitle[$key] : null,
                                        'alt'          => (isset($r->$imageFieldAlt)) ? $r->$imageFieldAlt[$key] : null,
                                        'path'         => (isset($r->$imageField[$key])) ? $uploadedImage->path : $uploadedImage->original_path,
                                        'path_resized' => (isset($r->$imageField[$key])) ? $uploadedImage->path_resized : $uploadedImage->original_path_resized,
                                        'category'     => (isset($r->$imageFieldCategory)) ? $r->$imageFieldCategory[$key] : $field,
                                        'sequence'     => (isset($r->$imageFieldSequence)) ? $r->$imageFieldSequence[$key] : 0,
                                        'name'         => (isset($r->$imageField[$key])) ? $uploadedImage->original_file_name : $uploadedImage->name,
                                        'size'         => (isset($r->$imageField[$key])) ? $uploadedImage->file_size : $uploadedImage->size,
                                        'caption'      => (isset($r->$imageFieldCaption)) ? $r->$imageFieldCaption[$key] : null
                                    ];

                                    $record->uploadImage($payload);
                                } else {
                                    $uploadedImage->update([
                                        'title'        => (isset($r->$imageFieldTitle)) ? $r->$imageFieldTitle[$key] : null,
                                        'alt'          => (isset($r->$imageFieldAlt)) ? $r->$imageFieldAlt[$key] : null,
                                        'sequence'     => (isset($r->$imageFieldSequence)) ? $r->$imageFieldSequence[$key] : 0,
                                        'caption'      => (isset($r->$imageFieldCaption)) ? $r->$imageFieldCaption[$key] : null
                                    ]);
                                }
                            } else {
                                if ($new) {
                                    if (isset($r->$imageFieldIdOld[$key])) {
                                        $existingImage = Image::where('id', $r->$imageFieldIdOld[$key])->first();

                                        $existingImage->update([
                                            'title'        => (isset($r->$imageFieldTitle)) ? $r->$imageFieldTitle[$key] : null,
                                            'alt'          => (isset($r->$imageFieldAlt)) ? $r->$imageFieldAlt[$key] : null,
                                            'path'         => (isset($r->$imageField[$key])) ? $uploadedImage->path : $uploadedImage->original_path,
                                            'path_resized' => (isset($r->$imageField[$key])) ? $uploadedImage->path_resized : $uploadedImage->original_path_resized,
                                            'sequence'     => (isset($r->$imageFieldSequence)) ? $r->$imageFieldSequence[$key] : 0,
                                            'name'         => (isset($r->$imageField[$key])) ? $uploadedImage->original_file_name : $uploadedImage->name,
                                            'size'         => (isset($r->$imageField[$key])) ? $uploadedImage->file_size : $uploadedImage->size,
                                            'caption'      => (isset($r->$imageFieldCaption)) ? $r->$imageFieldCaption[$key] : null
                                        ]);
                                    } else {
                                        $payload = [
                                            'model'        => $model,
                                            'title'        => (isset($r->$imageFieldTitle)) ? $r->$imageFieldTitle[$key] : null,
                                            'alt'          => (isset($r->$imageFieldAlt)) ? $r->$imageFieldAlt[$key] : null,
                                            'path'         => (isset($r->$imageField[$key])) ? $uploadedImage->path : $uploadedImage->original_path,
                                            'path_resized' => (isset($r->$imageField[$key])) ? $uploadedImage->path_resized : $uploadedImage->original_path_resized,
                                            'category'     => (isset($r->$imageFieldCategory)) ? $r->$imageFieldCategory[$key] : $field,
                                            'sequence'     => (isset($r->$imageFieldSequence)) ? $r->$imageFieldSequence[$key] : 0,
                                            'name'         => (isset($r->$imageField[$key])) ? $uploadedImage->original_file_name : $uploadedImage->name,
                                            'size'         => (isset($r->$imageField[$key])) ? $uploadedImage->file_size : $uploadedImage->size,
                                            'caption'      => (isset($r->$imageFieldCaption)) ? $r->$imageFieldCaption[$key] : null
                                        ];

                                        $record->uploadImage($payload);
                                    }
                                } else {
                                    $uploadedImage->update([
                                        'title'        => (isset($r->$imageFieldTitle)) ? $r->$imageFieldTitle[$key] : null,
                                        'alt'          => (isset($r->$imageFieldAlt)) ? $r->$imageFieldAlt[$key] : null,
                                        'sequence'     => (isset($r->$imageFieldSequence)) ? $r->$imageFieldSequence[$key] : 0,
                                        'caption'      => (isset($r->$imageFieldCaption)) ? $r->$imageFieldCaption[$key] : null
                                    ]);
                                }
                            }
                        }
                    }
                }
                break;
        }
    }

    function getContentType($extension)
    {
        $result;
        switch ($extension) {
            case 'png':
            case 'PNG':
                $result = 'image/png';
                break;
            case 'svg':
            case 'SVG':
                $result = 'image/svg+xml';
                break;
            case 'gif':
            case 'GIF':
                $result = 'image/gif';
                break;
            case 'pdf':
            case 'PDF':
                $result = 'application/pdf';
                break;
            case 'ppt':
            case 'PPT':
                $result = 'application/vnd.ms-powerpoint';
                break;
            case 'pptx':
            case 'PPTX':
                $result = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
                break;
            case 'xls':
            case 'XLS':
                $result = 'application/vnd.ms-excel';
                break;
            case 'xlsx':
            case 'XLSX':
                $result = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                break;
            case 'docx':
            case 'DOCX':
                $result = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                break;
            case 'doc':
            case 'DOC':
                $result = 'application/msword';
                break;
            case 'txt':
            case 'TXT':
                $result = 'text/plain';
                break;
            case 'mp4':
            case 'MP4':
                $result = 'video/mp4';
                break;
            case 'webm':
            case 'WEBM':
                $result = 'video/webm';
                break;
            case 'mp3':
            case 'MP3':
                $result = 'audio/mp3';
                break;
            case 'ogg':
            case 'OGG':
                $result = 'audio/ogg';
                break;
            case 'wav':
            case 'WAV':
                $result = 'audio/wav';
                break;
        }

        return $result;
    }

    function deleteFile($oldFilePath, $oldFilePathResized)
    {
        $disk = 'public'; # s3 kapag s3. public kapag sa local lang isesave

        # delete the old file if it exists
        if ($oldFilePath != null) {
            Storage::disk($disk)->delete("$oldFilePath");
        }
        if ($oldFilePathResized != null) {
            Storage::disk($disk)->delete("$oldFilePathResized");
        }
    }

    function checkExternalFile($url)
    {
        $channel = curl_init();
        curl_setopt($channel, CURLOPT_URL, $url);
        // don't download content
        curl_setopt($channel, CURLOPT_NOBODY, 1);
        curl_setopt($channel, CURLOPT_FAILONERROR, 1);
        curl_setopt($channel, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($channel);
        curl_close($channel);
        if ($result !== FALSE) {
            return true;
        } else {
            return false;
        }
    }

    function uploadExternalFile($url, $oldFilePath = null, $oldFilePathResized = null, $model = null)
    {
        $disk = 'public'; # s3 kapag s3. public kapag sa local lang isesave

        # delete the old file if it exists
        if ($oldFilePath != null) {
            Storage::disk($disk)->delete("uploads/$oldFilePath");
        }
        if ($oldFilePathResized != null) {
            Storage::disk($disk)->delete("uploads/$oldFilePathResized");
        }

        $fileInfo = pathinfo($url);
        $filename = str_replace(array('’'), '-', $fileInfo['filename']);

        $extension = $fileInfo['extension'];
        $headers = get_headers($url, 1);
        $size = $headers["Content-Length"];

        $folderDate = Carbon::now()->format('Y-m-d');
        $folderTime = Carbon::now()->format('H-i-s-u');
        $filenameToStoreOriginal = $filename . '.' . $extension;
        $filenameToStore = $folderDate . '_' . $folderTime . '.' . $extension;

        $uploadPath = "uploads/" . $model . "/$folderDate/$filenameToStore";

        $unresizedFile = ImageInt::make($url)
            ->interlace()
            ->encode($extension, 80)
            ->orientate();

        Storage::disk($disk)->put($uploadPath, $unresizedFile->getEncoded(), 'public');

        # get the file size
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        $fileSize = number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];

        # upload resized file
        $resizedFile = ImageInt::make($url)->resize(750, 750, function ($c) {
            $c->aspectRatio();
            $c->upsize();
        })
            ->interlace()
            ->encode($extension, 80)
            ->orientate();

        $filenameToStore = $folderDate . '_' . $folderTime . '_resized.' . $extension;

        $uploadPathResized = "uploads/" . $model . "/$folderDate/$filenameToStore";

        Storage::disk($disk)->put($uploadPathResized, $resizedFile->getEncoded(), 'public');

        $record = (object) [
            'path' => $uploadPath,
            'path_resized' => $uploadPathResized,
            'original_file_name' => $filename,
            'main_original_file_name' =>  $filenameToStoreOriginal,
            'file_size' => $fileSize,
            'file_type' => $extension
        ];

        return $record;
    }

    function uploadFile($file, $oldFilePath = null, $oldFilePathResized = null, $model = null)
    {
        $disk = 'public'; # s3 kapag s3. public kapag sa local lang isesave

        # get the file size
        $size = filesize($file);
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        $fileSize = number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];

        # delete the old file if it exists
        if ($oldFilePath != null) {
            Storage::disk($disk)->delete("uploads/$oldFilePath");
        }
        if ($oldFilePathResized != null) {
            Storage::disk($disk)->delete("uploads/$oldFilePathResized");
        }

        $filenameWithExtension = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $filename = pathinfo($filenameWithExtension, PATHINFO_FILENAME);
        $folderDate = Carbon::now()->format('Y-m-d');
        $folderTime = Carbon::now()->format('H-i-s-u');
        $filenameToStoreOriginal = $filename . '.' . $extension;
        $filenameToStore = str_slug($filename, '-') . '_' . $folderTime . '.' . $extension;
        $otherAcceptedExtensions = [
            'wma', 'ogg', 'wav', 'mp3', 'mp4', 'webm', 'svg', 'gif', 'pdf', 'ppt', 'pptx', 'xls', 'xlsx', 'docx', 'doc', 'txt',
            'WMA', 'OGG', 'WAV', 'MP3', 'MP4', 'WEBM', 'SVG', 'GIF', 'PDF', 'PPT', 'PPTX', 'XLS', 'XLSX', 'DOCX', 'DOC', 'TXT'
        ];

        // $uploadPath = "uploads/$folderDate/$folderTime/$filenameToStore"; //old
        if (is_null($model)) {
            $uploadPath = "uploads/$folderDate/$filenameToStore";
        } else {
            $uploadPath = "uploads/" . $model . "/$folderDate/$filenameToStore";
        }

        # if the file is svg or gif, directly upload it and stop the function immediately by returning the path names
        if (in_array($extension, $otherAcceptedExtensions)) {
            Storage::disk($disk)->put($uploadPath, file_get_contents($file), [
                'visibility' => 'public',
                'ContentType' => $this->getContentType($extension)
            ]);

            $toReturn = (object) [
                'path' => $uploadPath,
                'path_resized' => $uploadPath,
                'original_file_name' => $filenameToStore,
                'file_size' => $fileSize,
                'file_type' => $extension,
                'main_original_file_name' =>  $filenameToStoreOriginal,
            ];

            return $toReturn;
        }

        // $filenameToStore_resized = $filename . '_thumbnail.' . $extension;
        $filenameToStore_resized = str_slug($filename, '-') . '_' . $folderTime . '_resized.' . $extension;
        // $uploadPathResized = "uploads/$folderDate/$folderTime/$filenameToStore_resized"; //old
        if (is_null($model)) {
            $uploadPathResized = "uploads/all_files/$folderDate/$filenameToStore_resized";
        } else {
            $uploadPathResized = "uploads/" . $model . "/$folderDate/$filenameToStore_resized";
        }

        # check if image extension is png if png straight upload using Storage function and not use the image intervention
        if (strtolower($extension) == 'png') {
            # main image
            Storage::disk($disk)->put($uploadPath, file_get_contents($file), [
                'visibility' => 'public',
                'ContentType' => $this->getContentType($extension)
            ]);

            #resize image
            Storage::disk($disk)->put($uploadPathResized, file_get_contents($file), [
                'visibility' => 'public',
                'ContentType' => $this->getContentType($extension)
            ]);
        } else {
            $unresizedFile = ImageInt::make($file->getRealPath())->interlace()->encode($extension, 80)->orientate();
            Storage::disk($disk)->put($uploadPath, $unresizedFile->getEncoded(), 'public');

            # upload resized file
            $resizedFile = ImageInt::make($file->getRealPath())->resize(750, 750, function ($c) {
                $c->aspectRatio();
                $c->upsize();
            })->interlace()->encode($extension, 80)
                ->orientate();
            Storage::disk($disk)->put($uploadPathResized, $resizedFile->getEncoded(), 'public');
        }

        $toReturn = (object) [
            // 'path' => "uploads/$folderDate/$folderTime/$filenameToStore",
            // 'path_resized' => "uploads/$folderDate/$folderTime/$filenameToStore_resized",
            'path' => $uploadPath,
            'path_resized' => $uploadPathResized,
            'original_file_name' => $filenameWithExtension,
            'main_original_file_name' =>  $filenameToStoreOriginal,
            'file_size' => $fileSize,
            'file_type' => $extension
        ];

        // make a copy of images uploaded, if webp convert to png | if png/jpg/jpeg converto to webp
        $original_path = config('app.api_url') . "/storage/$uploadPath";
        $original_path_resized = config('app.api_url') . "/storage/$uploadPathResized";
        $this->copyImage(0, $uploadPath, $original_path, $disk);
        $this->copyImage(0, $uploadPathResized, $original_path_resized, $disk);

        return $toReturn;
    }

    function fileIsImage($file)
    {
        $result = false;
        if (@is_array(getimagesize($file))) {
            $result = true;
        }

        return $result;
    }

    function validateYoutubeLink($link)
    {
        $youtubeId;
        try {
            preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $link, $match);
            $youtubeId = $match[1];
        } catch (Exception $e) {
            return [
                'valid' => false,
                'thumbnail' => null
            ];
        }

        $response = Http::get("https://www.googleapis.com/youtube/v3/videos?part=snippet&part=contentDetails&id=$youtubeId&key=AIzaSyANq7YUBMI2TYH9yiLlztUlyo-sZWXw4Gc");

        $result = $response->json()['pageInfo']['totalResults'];

        if ($result == 1) {
            if (isset($response->json()['items'][0]['snippet']['thumbnails']['maxres'])) {
                $thumbnail = $response->json()['items'][0]['snippet']['thumbnails']['maxres']['url'];
            } else if (isset($response->json()['items'][0]['snippet']['thumbnails']['standard'])) {
                $thumbnail = $response->json()['items'][0]['snippet']['thumbnails']['standard']['url'];
            } else if (isset($response->json()['items'][0]['snippet']['thumbnails']['high'])) {
                $thumbnail = $response->json()['items'][0]['snippet']['thumbnails']['high']['url'];
            } else if (isset($response->json()['items'][0]['snippet']['thumbnails']['medium'])) {
                $thumbnail = $response->json()['items'][0]['snippet']['thumbnails']['medium']['url'];
            } else {
                $thumbnail = $response->json()['items'][0]['snippet']['thumbnails']['default']['url'];
            }

            $title = $response->json()['items'][0]['snippet']['title'];
            $description = $response->json()['items'][0]['snippet']['description'];
            $published = $response->json()['items'][0]['snippet']['publishedAt'];
            $duration = $response->json()['items'][0]['contentDetails']['duration'];

            return (object) [
                'valid' => true,
                'youtubeId' => $youtubeId,
                'thumbnail' => $thumbnail,
                'title' => $title,
                'description' => $description,
                'published' => $published,
                'duration' => $duration,
            ];
        } else {
            return (object) [
                'valid' => false,
                'youtubeId' => null,
                'thumbnail' => null,
                'title' => null,
                'description' => null,
                'published' => null,
                'duration' => null
            ];
        }
    }

    /**
     * [copyImage function]
     * @param [integer] $id - Id of image to copy
     * @param [String] $image  - image path to copy
     * @param [String] $original_path  - image path to copy
     * @param [String] $disk  - disk
     */
    public function copyImage($id, $image, $original_path, $disk)
    {
        $path = [];
        $image_path = $image;
        $new_path = dirname($image);
        $file_name = pathinfo($image, PATHINFO_FILENAME);
        $file_extension = pathinfo($image, PATHINFO_EXTENSION);
        $new_file_path = '';
        $extension = '';
        // check if image path is existing in drive
        if (strtolower($file_extension) != 'svg') {
            $exist = Storage::disk($disk)->exists($image_path);
            if ($exist) {
                // check if image is png/jpg/jpeg/webp
                switch (strtolower($file_extension)) {
                    case 'png':
                    case 'jpg':
                    case 'jpeg':
                        $new_file_path = $new_path . '/' . $file_name . ".webp";
                        $extension = 'webp';
                        break;
                    case 'webp':
                        $new_file_path = $new_path . '/' . $file_name . ".jpg";
                        $extension = 'jpg';
                        break;
                }

                // check new file path is empty if empty the image is not png/jpg/jpeg/webp
                if ($new_file_path != '') {
                    $new_file_exist = Storage::disk($disk)->exists($new_file_path);
                    // check if new file is existing
                    if ($new_file_exist) {
                        $path = array(
                            'id' => $id,
                            'image_path' => $image_path,
                            'new_file_path' => $new_file_path,
                            'message' => 'no need to copy'
                        );
                    } else {
                        // upload resized file
                        $uploaded = ImageInt::make($original_path)
                            ->interlace()
                            ->encode($extension, 80)
                            ->orientate();

                        $copied = Storage::disk($disk)->put($new_file_path, $uploaded->getEncoded());

                        // check if copied successfully
                        if ((!$copied)) {
                            $path = array(
                                'id' => $id,
                                'image_path' => $image_path,
                                'new_file_path' => $new_file_path,
                                'message' => 'not copied'
                            );
                        } else {
                            $path = array(
                                'id' => $id,
                                'image_path' => $image_path,
                                'new_file_path' => $new_file_path,
                                'message' => 'copied'
                            );
                        }
                    }
                } else {
                    $path = array(
                        'id' => $id,
                        'image_path' => $image_path,
                        'new_file_path' => $new_file_path,
                        'message' => 'no need to copy'
                    );
                }
            } else {
                $path = array(
                    'id' => $id,
                    'image_path' => $image_path,
                    'new_file_path' => $new_file_path,
                    'message' => 'not exist'
                );
            }
        } else {
            $path = array(
                'id' => $id,
                'image_path' => $image_path,
                'new_file_path' => $new_file_path,
                'message' => 'no need to copy'
            );
        }

        return $path;
    }

    /**
     * [filenameChange function]
     * @param [Object] $data
     */
    public function filenameChange($data)
    {
        $image = Image::where('id', $data->id)->first();
        if ($image) {
            $rename = false;
            $renameCopy = false;
            $folderTime = Carbon::create($image->updated_at)->format('H-i-s-u');
            $originalFilename = $image->name;
            $path = dirname($image->filename);

            $filename = pathinfo($originalFilename, PATHINFO_FILENAME);
            $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
            $extensionCopy = '';

            if (strtolower($extension) != 'svg') {
                switch (strtolower($extension)) {
                    case 'png':
                    case 'jpg':
                    case 'jpeg':
                        $extensionCopy = 'webp';
                        break;
                    case 'webp':
                        $extensionCopy = 'png';
                        break;
                }
            }

            $newFilename = str_slug($filename, '-') . '_' . $folderTime . '.' . $extension;
            $newFilenameResized = str_slug($filename, '-') . '_' . $folderTime . '_resized.' . $extension;
            $newPath = "$path/$newFilename";
            $newPathResized = "$path/$newFilenameResized";

            if (Storage::disk('public')->exists($image->filename) && Storage::disk('public')->exists($image->fileresized)) {
                if ($extensionCopy != '') {
                    $convertedFilename = pathinfo($image->filename, PATHINFO_FILENAME);
                    $convertedFilenameResized = pathinfo($image->fileresized, PATHINFO_FILENAME);
                    $oldCopyPath = "$path/$convertedFilename.$extensionCopy";
                    $oldCopyPathResized = "$path/$convertedFilenameResized.$extensionCopy";

                    $newCopyFilename = str_slug($filename, '-') . '_' . $folderTime . '.' . $extensionCopy;
                    $newCopyFilenameResized = str_slug($filename, '-') . '_' . $folderTime . '_resized.' . $extensionCopy;
                    $newCopyPath = "$path/$newCopyFilename";
                    $newCopyPathResized = "$path/$newCopyFilenameResized";
                    if (Storage::disk('public')->exists($oldCopyPath) && Storage::disk('public')->exists($oldCopyPathResized)) {
                        rename(getcwd() . "/storage/$oldCopyPath", getcwd() . "/storage/$newCopyPath");
                        rename(getcwd() . "/storage/$oldCopyPathResized", getcwd() . "/storage/$newCopyPathResized");
                        $renameCopy = true;
                    }
                }
                rename(getcwd() . "/storage/$image->filename", getcwd() . "/storage/$newPath");
                if ($extension != 'svg') {
                    rename(getcwd() . "/storage/$image->fileresized", getcwd() . "/storage/$newPathResized");
                }
                $rename = true;
                $image->update([
                    'path' => $newPath,
                    'path_resized' => $newPathResized
                ]);
            }

            return (object) [
                'success' => $rename,
                'successCopy' => $renameCopy,
                'image' => (object) [
                    'id' => $image->id,
                    'name' => $image->name,
                    'path' => $image->filename,
                    'path_resized' => $image->fileresized,
                    'model' => $image->model,
                    'category' => $image->category,
                ],
            ];
        }
    }

    public function getMonthCode($month)
    {
        $month = strtoupper($month);
        switch ($month) {
            case 'MARCH':
                $month = 'MR';
                break;
            case 'JUNE':
                $month = 'JN';
                break;
            case 'JULY':
                $month = 'JY';
                break;
            default:
                $month = substr($month, 0, 2);
                break;
        }

        return $month;
    }

    /**
     * [generateWatchSKU function]
     * @param string $date - Watch Date of entry (Y-m-d)
     * @param object $brand  - brand data
     * @param string $type  - product type
     * @param object $productCategory  - category ID
     */
    public function generateWatchSKU($dateOfEntry, $brand, $type, $productCategory = null)
    {
        $sku = '';
        $products = [];
        $month = $this->getMonthCode(Carbon::create($dateOfEntry)->format('F'));
        $startDate = Carbon::create($dateOfEntry)->format('Y-m') . '-01';
        $lastDate = Carbon::create($dateOfEntry)->format('Y-m') . '-31';
        switch ($type) {
            case 'watch':
                $products = Watch::whereHas('detail', function ($q) use ($brand, $startDate, $lastDate, $type) {
                    $q->where('brand_id', $brand->id)
                        ->whereBetween('date_of_entry', [$startDate, $lastDate]);
                })
                    ->get();

                $sku = $brand->code . $month . Carbon::create($dateOfEntry)->format('y');
                break;
            case 'accessories':
                $products = Accessories::whereHas('detail', function ($q) use ($brand, $startDate, $lastDate, $type) {
                    $q->whereBetween('date_of_entry', [$startDate, $lastDate]);
                })
                    ->where('product_category_id', $productCategory->id)
                    ->get();
                $sku = $brand->code . 'ACC' . $productCategory->code;
                break;
        }

        $productCount = '000';

        if (count($products) > 0) {
            switch (strlen(count($products))) {
                case 1:
                    $productCount = '00' . (count($products) + 1);
                    break;
                case 2:
                    $productCount = '0' . (count($products) + 1);
                    break;
                default:
                    $productCount = count($products);
                    break;
            }
        } else {
            $productCount = '001';
        }

        $sku = $sku . $productCount;
        // $sku = $brand->code.$month.Carbon::create($dateOfEntry)->format('y').$productCount;

        return $sku;
    }

    /**
     * [checkRecordById function]
     * @param string $id
     * @param string $model
     */
    public function checkRecordById($id, $model)
    {
        $MODEL = '\App\Models\\' . $model;
        $record =  $MODEL::where('id', $id)->first();
        return $record;
    }

    /**
     * [computeTax function]
     * @param integer $value
     */
    public function computeTax($value, $percentage)
    {
        $taxValue = 0;
        $tax = TaxSetting::first();
        if ($tax) {
            $percentage = (float) $tax->percentage / 100;
        }

        $taxValue = number_format((float) $value * (float) $percentage, 2, '.', '');

        return $taxValue;
    }

    /**
     * [getFilterValues function]
     * @param string $filterType
     */
    public function getFilterValues($filterType, $type = 'watch')
    {;
        switch ($filterType) {
            case 'transaction-status':
                return [
                    (object) [
                        'id' => 'processing',
                        'name' => 'Processing',
                    ],
                    (object) [
                        'id' => 'shipped',
                        'name' => 'Shipped',
                    ],
                    (object) [
                        'id' => 'delivered',
                        'name' => 'Delivered',
                    ],
                    (object) [
                        'id' => 'cancelled',
                        'name' => 'Cancelled',
                    ],
                    (object) [
                        'id' => 'declined',
                        'name' => 'Declined',
                    ],
                    (object) [
                        'id' => 'refunded',
                        'name' => 'Refunded',
                    ],
                    (object) [
                        'id' => 'completed',
                        'name' => 'Completed',
                    ],
                ];
                break;
            case 'status':
                return [
                    (object) [
                        'id' => 'anan-stock',
                        'name' => 'Anan Stock',
                    ],
                    (object) [
                        'id' => 'awaiting-approval-for-release',
                        'name' => 'Awaiting Approval (for release)',
                    ],
                    (object) [
                        'id' => 'for-appraisal',
                        'name' => 'For Appraisal',
                    ],
                    (object) [
                        'id' => 'for-handling',
                        'name' => 'For Handling',
                    ],
                    (object) [
                        'id' => 'for-pull-out',
                        'name' => 'For Pull Out',
                    ],
                    (object) [
                        'id' => 'for-release-(by-sales-rep)',
                        'name' => 'For Release (By Sales Rep)',
                    ],
                    (object) [
                        'id' => 'for-repair',
                        'name' => 'For Repair',
                    ],
                    (object) [
                        'id' => 'for-sale',
                        'name' => 'For Sale',
                    ],
                    (object) [
                        'id' => 'for-shoot',
                        'name' => 'For Shoot',
                    ],
                    (object) [
                        'id' => 'pull-out',
                        'name' => 'Pull Out',
                    ],
                    (object) [
                        'id' => 'repair-done',
                        'name' => 'Repair Done',
                    ],
                    (object) [
                        'id' => 'replaced',
                        'name' => 'Replaced',
                    ],
                    (object) [
                        'id' => 'sold',
                        'name' => 'Sold',
                    ],
                ];
                break;
            case 'accessories-status':
                return [

                    (object) [
                        'id' => 'anan-stock',
                        'name' => 'Anan Stock',
                    ],
                    (object) [
                        'id' => 'awaiting-approval-for-release',
                        'name' => 'Awaiting Approval (for release)',
                    ],
                    (object) [
                        'id' => 'for-appraisal',
                        'name' => 'For Appraisal',
                    ],
                    (object) [
                        'id' => 'for-handling',
                        'name' => 'For Handling',
                    ],
                    (object) [
                        'id' => 'for-pull-out',
                        'name' => 'For Pull Out',
                    ],
                    (object) [
                        'id' => 'for-release-(by-sales-rep)',
                        'name' => 'For Release (By Sales Rep)',
                    ],
                    (object) [
                        'id' => 'for-repair',
                        'name' => 'For Repair',
                    ],
                    (object) [
                        'id' => 'for-sale',
                        'name' => 'For Sale',
                    ],
                    (object) [
                        'id' => 'for-shoot',
                        'name' => 'For Shoot',
                    ],
                    (object) [
                        'id' => 'pull-out',
                        'name' => 'Pull Out',
                    ],
                    (object) [
                        'id' => 'repair-done',
                        'name' => 'Repair Done',
                    ],
                    (object) [
                        'id' => 'replaced',
                        'name' => 'Replaced',
                    ],
                    (object) [
                        'id' => 'spare-stock',
                        'name' => 'Spare/Stock',
                    ],
                    (object) [
                        'id' => 'sold',
                        'name' => 'Sold',
                    ],
                ];
                break;
            case 'condition':
                return [
                    (object) [
                        'id' => 'brand-new',
                        'name' => 'Brand New',
                    ],
                    (object) [
                        'id' => 'pre-owned',
                        'name' => 'Pre-Owned',
                    ],
                    (object) [
                        'id' => 'vintage',
                        'name' => 'Vintage',
                    ],
                ];
                break;
            case 'inventory':
                return [
                    (object) [
                        'id' => 'on-hand',
                        'name' => 'On Hand',
                    ],
                    (object) [
                        'id' => 'released',
                        'name' => 'Released',
                    ],
                    (object) [
                        'id' => 'incoming',
                        'name' => 'Incoming',
                    ],
                    (object) [
                        'id' => 'outgoing',
                        'name' => 'Outgoing',
                    ],
                ];
                break;
            case 'inventory_condition':
                return [
                    (object) [
                        'id' => 'brand-new',
                        'name' => 'Brand New',
                    ],
                    (object) [
                        'id' => 'excellent',
                        'name' => 'Excellent',
                    ],
                    (object) [
                        'id' => 'very-good',
                        'name' => 'Very Good',
                    ],
                    (object) [
                        'id' => 'good',
                        'name' => 'Good',
                    ],
                    (object) [
                        'id' => 'defective',
                        'name' => 'Defective',
                    ],
                ];
                break;
            case 'availability':
                return [
                    (object) [
                        'id' => 'available',
                        'name' => 'Available',
                    ],
                    (object) [
                        'id' => 'pre-order',
                        'name' => 'Pre-Order',
                    ],
                    (object) [
                        'id' => 'price-by-request',
                        'name' => 'Price By Request',
                    ],
                    (object) [
                        'id' => 'source',
                        'name' => 'Source',
                    ],
                ];
                break;
            case 'brand':
                return Brand::select(
                    'id',
                    'name',
                    'slug',
                    'code'
                )
                    ->orderBy('name')
                    ->get();
                break;
            case 'inclusion':
                return Inclusion::select(
                    'id',
                    'name',
                )
                    ->orderBy('name')
                    ->get();
                break;
            case 'interest':
                return Interest::select(
                    'id',
                    'name',
                )
                    ->orderBy('name')
                    ->get();
                break;
            case 'location':
                return ProductLocation::select(
                    'id',
                    'name',
                )
                    ->orderBy('name')
                    ->get();
                break;
            case 'inventory_location':
                return ProductInventoryLocation::select(
                    'id',
                    'name',
                )
                    ->orderBy('name')
                    ->get();
                break;
            case 'shelf':
                return ProductShelfLocation::select(
                    'id',
                    'name',
                )
                    ->orderBy('name')
                    ->get();
                break;
            case 'currency':
                return Currency::select(
                    'id',
                    'name',
                )
                    ->orderBy('name')
                    ->get();
                break;
            case 'category':
                return ProductCategory::select(
                    'id',
                    'name',
                    'slug'
                )
                    ->where('type', $type)
                    ->orderBy('name')
                    ->get();
                break;
            case 'case_size':
                return [
                    (object) [
                        'name' => '42Mm And Up',
                        'from' => 42,
                        'to'   => 100000000000
                    ],
                    (object) [
                        'name' => '38Mm To 41Mm',
                        'from' => 38,
                        'to'   => 41
                    ],
                    (object) [
                        'name' => '37Mm and Below',
                        'from' => 0,
                        'to'   => 37
                    ],
                ];
                break;
            case 'movement':
                return [
                    (object) [
                        'id' => 'mechanical',
                        'name' => 'Mechanical',
                    ],
                    (object) [
                        'id' => 'automatic',
                        'name' => 'Automatic',
                    ],
                    (object) [
                        'id' => 'quartz',
                        'name' => 'Quartz',
                    ],
                ];
                break;
            case 'materials':
                return ProductMaterial::orderby('name')
                    ->get();
                break;
            case 'ins-product-sort':
                return [
                    (object) [
                        'name' => 'Brand (A - Z)',
                        'column' => 'brand',
                        'order' => 'asc'
                    ],
                    (object) [
                        'name' => 'Brand (Z - A)',
                        'column' => 'brand',
                        'order' => 'desc'
                    ],
                    (object) [
                        'name' => 'Model (A - Z)',
                        'column' => 'model',
                        'order' => 'asc'
                    ],
                    (object) [
                        'name' => 'Model (Z - A)',
                        'column' => 'model',
                        'order' => 'desc'
                    ],
                    (object) [
                        'name' => 'Order (Newest - Oldest)',
                        'column' => 'date_of_entry',
                        'order' => 'desc'
                    ],
                    (object) [
                        'name' => 'Order (Oldest - Newest)',
                        'column' => 'date_of_entry',
                        'order' => 'asc'
                    ],
                ];
                break;
            case 'ins-customer-sort':
                return [
                    (object) [
                        'name' => 'First Name (A - Z)',
                        'column' => 'first_name',
                        'order' => 'asc'
                    ],
                    (object) [
                        'name' => 'First Name (Z - A)',
                        'column' => 'first_name',
                        'order' => 'desc'
                    ],
                ];
                break;
            case 'ins-services-sort':
                return [
                    (object) [
                        'name' => 'Date (Newest - Oldest)',
                        'column' => 'created_at',
                        'order' => 'desc'
                    ],
                    (object) [
                        'name' => 'Date (Oldest - Newest)',
                        'column' => 'created_at',
                        'order' => 'asc'
                    ],
                ];
                break;
            case 'ins-services':
                return [
                    (object) [
                        'name' => 'For Sell',
                        'id' => 'sell'
                    ],
                    (object) [
                        'name' => 'For Trade',
                        'id' => 'trade'
                    ],
                    (object) [
                        'name' => 'For Consign',
                        'id' => 'consign'
                    ],
                    (object) [
                        'name' => 'For Source',
                        'id' => 'source'
                    ],
                ];
                break;
            case 'specialists':
                return UserSpecialistDetail::select(
                    'id',
                    'user_id',
                    'first_name',
                    'last_name',
                    'full_name',
                )
                    ->whereHas('user', function ($q) {
                        $q->where('enabled', 1);
                    })
                    ->get();
                break;
            case 'repair-types':
                return RepairType::orderBy('name')
                    ->get();
                break;
            case 'appointment_time':
                return [
                    (object) [
                        'id' => '10:00:00',
                        'name' => '10:00 AM',
                    ],
                    (object) [
                        'id' => '11:00:00',
                        'name' => '11:00 AM',
                    ],
                    (object) [
                        'id' => '12:00:00',
                        'name' => '12:00 NN',
                    ],
                    (object) [
                        'id' => '13:00:00',
                        'name' => '01:00 PM',
                    ],
                    (object) [
                        'id' => '14:00:00',
                        'name' => '02:00 PM',
                    ],
                    (object) [
                        'id' => '15:00:00',
                        'name' => '03:00 PM',
                    ],
                    (object) [
                        'id' => '16:00:00',
                        'name' => '04:00 PM',
                    ],
                    (object) [
                        'id' => '17:00:00',
                        'name' => '05:00 PM',
                    ],
                    (object) [
                        'id' => '18:00:00',
                        'name' => '06:00 PM',
                    ],
                    (object) [
                        'id' => '19:00:00',
                        'name' => '07:00 PM',
                    ],
                ];
                break;
        }
    }

    /**
     * [setUserActivities function]
     * @param Request $request
     * @param string $type
     * @param object $record
     */
    public function setUserActivities($request, $type, $record)
    {
        $activity = UserCustomerActivity::where('user_id', $request->id)
            ->first();
        if ($activity) {
            $toPush = [];
            $isExisting = false;
            foreach ($activity->$type ? json_decode($activity->$type) : [] as $value) {
                if ($value->id == $record->id) {
                    $value->count++;
                    $isExisting = true;
                }

                array_push($toPush, $value);
            }

            if (!$isExisting) {
                array_push($toPush, (object) [
                    'id' => $record->id,
                    'count' => 1
                ]);
            }

            switch ($type) {
                case 'brand_visits':
                    $activity->update([
                        'brand_visits' => $toPush
                    ]);
                    break;
                case 'brand_checkouts':
                    $activity->update([
                        'brand_checkouts' => $toPush
                    ]);
                    break;
                case 'article_visits':
                    $activity->update([
                        'article_visits' => $toPush
                    ]);
                    break;
            }
        } else {
            $activity = UserCustomerActivity::create([
                'user_id' => $request->id,
            ]);
            switch ($type) {
                case 'brand_visits':
                    $activity->update([
                        'brand_visits' => json_encode([
                            (object) [
                                'id' => $record->id,
                                'count' => 1
                            ]
                        ])
                    ]);
                    break;
                case 'brand_checkouts':
                    $activity->update([
                        'brand_checkouts' => json_encode([
                            (object) [
                                'id' => $record->id,
                                'count' => 1
                            ]
                        ])
                    ]);
                    break;
                case 'setUserActivities':
                    $activity->update([
                        'setUserActivities' => json_encode([
                            (object) [
                                'id' => $record->id,
                                'count' => 1
                            ]
                        ])
                    ]);
                    break;
            }
        }
    }

    /**
     * [countProductVisit function]
     * @param Request $request
     * @param string $type
     * @param object $record
     */
    public function countProductVisit($id, $type, $request)
    {
        $visit = ProductVisit::where([
            'pivot_id' => $id,
            'type' => $type,
            'ip'  => $request->ip()
        ])
            ->first();

        if (!$visit) {
            ProductVisit::create([
                'pivot_id' => $id,
                'type' => $type,
                'ip' => $request->ip()
            ]);
        }
    }

    public function sortArray($array, $type, $field)
    {
        switch ($type) {
            case 'asc':
                usort($array, function ($a, $b) use ($field) {
                    return $a->$field <=> $b->$field;
                });
                break;
            case 'desc':
                usort($array, function ($a, $b) use ($field) {
                    if ($a->$field == $b->$field) return 0;
                    return $a->$field < $b->$field ? 1 : -1;
                });
                break;
        }

        return $array;
    }

    public function userNewsletter($email, $type)
    {
        $newsletter = Newsletter::where('email', $email)->first();
        switch ($type) {
                // unsubscribe
            case 0:
                if ($newsletter) {
                    $newsletter->forceDelete();
                }
                break;
                // subscribe
            case 1:
                Newsletter::updateOrCreate(
                    [
                        'email' => $email,
                    ],
                    [
                        'date_subscribed' => now()
                    ]
                );
                break;
        }
    }

    public function getAppointmentDisabledDates($appointments)
    {
        $timeslot = $this->getFilterValues('appointment_time');
        $disabled_dates = [];

        foreach ($appointments as $key => $value) {
            $time_list = [];
            $is_disabled = true;
            foreach ($timeslot as $time) {
                $is_exists = false;
                foreach ($value as $date) {
                    if ($time->id == $date->time_visit) {
                        $is_exists = true;
                        break;
                    }
                }
                array_push($time_list, (object) [
                    'time' => $time->id,
                    'is_exists' => $is_exists
                ]);
            }

            foreach ($time_list as $time) {
                if (!$time->is_exists) {
                    $is_disabled = false;
                }
            }

            if ($is_disabled) {
                array_push($disabled_dates, $key);
            }
        }

        return $disabled_dates;
    }

    public function getAppointmentAvailableTime($appointments)
    {
        $timeslot = $this->getFilterValues('appointment_time');
        $available_time = [];

        foreach ($timeslot as $time) {
            $is_exists = false;
            foreach ($appointments as $value) {
                if ($time->id == $value->time_visit) {
                    $is_exists = true;
                    break;
                }
            }
            if (!$is_exists) {
                array_push($available_time, $time);
            }
        }

        return $available_time;
    }
}
