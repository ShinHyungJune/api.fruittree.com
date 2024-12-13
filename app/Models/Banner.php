<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @deprecated
 */
class Banner extends Model implements HasMedia
{
    protected $guarded = ['id'];

    use HasFactory, InteractsWithMedia;

    public function registerMediaCollections():void
    {
        $this->addMediaCollection('img')->singleFile();
    }

    // 파일 1개만 첨부 가능해야할 경우 예시
    public function getImgAttribute()
    {
        if($this->hasMedia('img')) {
            $media = $this->getMedia('img')[0];

            return [
                "id" => $media->id,
                "name" => $media->file_name,
                "url" => $media->getFullUrl()
            ];
        }

        return null;
    }

    // 파일 여러개 첨부 가능할 경우, 대표이미지를 뿌려줘야하는 경우
    public function getImgAttribute()
    {
        if($this->hasMedia('imgs')) {
            $media = $this->getMedia('imgs')[0];

            return [
                "id" => $media->id,
                "name" => $media->file_name,
                "url" => $media->getFullUrl()
            ];
        }

        return null;
    }


    // 파일 여러개 첨부 가능할 경우 예시
    public function getImgsAttribute()
    {
        $medias = $this->getMedia("imgs");

        $items = [];

        foreach($medias as $media){
            $items[] = [
                "id" => $media->id,
                "name" => $media->file_name,
                "url" => $media->getFullUrl()
            ];
        }

        return $items;
    }
}
