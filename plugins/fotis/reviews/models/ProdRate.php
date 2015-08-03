<?php namespace Fotis\Reviews\Models;

use Model;

/**
 * ProdRate Model
 */
class ProdRate extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'fotis_reviews_prod_rates';

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