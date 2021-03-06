<?php

namespace Angrydeer\Productso\Models;

use Kalnoy\Nestedset\Node;
use Angrydeer\Attachfiles\AttachableTrait;
use Angrydeer\Attachfiles\AttachableInterface;
use Request;
use Sentinel;

class PrsoCategory extends Node implements AttachableInterface
{
    use AttachableTrait;

    public static $productPerPage = 20;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'slug', '_lft', '_rgt', 'parent_id', 'note', 'desc', 'showtop', 'showside', 'showbottom', 'showcontent',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany('Angrydeer\Productso\Models\PrsoProduct');
    }

    /**
     * @param $slug
     */
    public function setSlugAttribute($slug)
    {

        if($slug=='') $slug = str_slug(Request::get('name'));
        if($cat= self::where('slug',$slug)->first()){
            $idmax=self::max('id')+1;
            if(isset($this->attributes['id']))
            {
                if ($this->attributes['id'] != $cat->id ){
                    $slug=$slug.'_'.++$idmax;
                }
            }
            else
            {
                if (self::where('slug',$slug)->count() > 0)
                    $slug=$slug.'_'.++$idmax;
            }
        }
        $this->attributes['slug']=$slug;
    }

    /**
     * @param $value
     * @return array
     */
    public function getPhotosAttribute($value)
    {
        return array_pluck($this->attaches()->get()->toArray(), 'filename');
    }

    /**
     * @param $images
     */
    public function setPhotosAttribute($images)
    {
        $imgtitles = Request::get('imgtitle');
        $imgalts = Request::get('imgalt');
        $imgdescs = Request::get('imgdesc');
        $this->save();
        $i=0;
        foreach($images as $image)
        {
            $this->updateOrNewAttach($image, $imgtitles[$i], $imgalts[$i], $imgdescs[$i]);
            $i++;
        }
        $path = config('admin.imagesUploadDirectory').'/'.Sentinel::check()->id;
        $files = glob(public_path($path)."/*");
        if (count($files) > 0) {
            foreach ($files as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
        $this->keepOnly($images);
    }

    public static function getRootTop()
    {
        return self::where('showtop', true)->whereIsRoot()->get();
    }

}
