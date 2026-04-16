<?php

namespace Theme\Jobbox\Http\Resources;

use Botble\Base\Facades\BaseHelper;
use Botble\Media\Facades\RvMedia;
use Botble\Testimonial\Models\Testimonial;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Testimonial
 */
class TestimonialResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'content' => BaseHelper::clean($this->content),
            'company' => $this->company,
            'image' => RvMedia::getImageUrl($this->image, 'thumb', false, RvMedia::getDefaultImage()),
        ];
    }
}
