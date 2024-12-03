<?php

namespace App\Http\Resources\Post;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BoardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //return parent::toArray($request);
        $return = [
            ...$this->only(['id', 'name_ko', 'can_create']),
            'category_items' => BoardCategoryResource::collection($this->categoryItems),
        ];
        //*
        return $return;
        /*/
        $comments = ['id' => '기본키', 'name_ko' => '게시판이름', 'can_create' => '게시글 등록가능여부'];
        return getScribeResponseFile($return, 'boards', $comments);
        //*/

    }
}
