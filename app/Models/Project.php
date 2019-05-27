<?php

namespace App\Models;

use App\Filters\Filterable;
use Illuminate\Support\Str;
use Backpack\CRUD\CrudTrait;
use Intervention\Image\Facades\Image;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Backpack\CRUD\ModelTraits\SpatieTranslatable\HasTranslations;
use Prologue\Alerts\Facades\Alert;

class Project extends Model
{
    use CrudTrait;
    use HasTranslations;
    use Filterable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'projects';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'category_id',
        'description',
        'image',
        'links',
        'status',
        'visible',
        'order',
    ];

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    public $translatable = [
        'title',
        'slug',
        'description',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'visible' => 'boolean',
    ];

    /**
     * Fields that are dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function (Project $project) {
            $project->slug = Str::slug($project->title);
        });
        static::updating(function (Project $project) {
            $project->slug = Str::slug($project->title);
        });
        static::deleting(function ($obj) {
            Storage::disk('public')->delete($obj->image);
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function links()
    {
        return $this->hasMany(Link::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function hasTag(Tag $tag)
    {
        if ($this->relationLoaded('tags')) {
            return $this->tags->contains($tag);
        }

        return $this->tags()
            ->wherePivot('tag_id', $tag->id)
            ->exists();
    }

    public function setImageAttribute($value)
    {
        $attribute_name = 'image';
        $disk = 'public';
        $destination_path = 'projects';

        if (is_null($value)) {
            if (Storage::disk($disk)->delete($this->{$attribute_name})) {
                $this->attributes[$attribute_name] = null;
            }
        }

        if (Str::startsWith($value, 'data:image'))
        {
            preg_match("/^data:image\/(.*);base64/i", $value, $match);
            $extension = $match[1];
            $image = Image::make($value);
            if (!is_null($image)) {
                $filename = md5($value.time()).'.'.$extension;
                try {
                    Storage::disk($disk)->put($destination_path.'/'.$filename, $image->stream());
                    $this->attributes[$attribute_name] = $destination_path.'/'.$filename;
                } catch (\InvalidArgumentException $argumentException) {
                    Alert::error($argumentException->getMessage())->flash();
                    $this->attributes[$attribute_name] = null;
                }
            }
        } else {
            $this->attributes[$attribute_name] = $value;
        }
    }

    public function setLinksAttribute($value)
    {
        $this->links()->delete();
        $links = [];
        if(json_decode($value)) {
            foreach (json_decode($value) as $link) {
                $links[] = new Link([
                    'title' => $link->title,
                    'url' => $link->url,
                    'icon' => $link->icon,
                ]);
            }
        }
        if ($links) {
            $this->links()->saveMany($links);
        }
    }
}
