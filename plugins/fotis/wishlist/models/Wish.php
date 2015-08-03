<?php namespace Fotis\Wishlist\Models;

use Model;

/**
 * Wish Model
 */
class Wish extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'fotis_wishlist_wishes';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

}