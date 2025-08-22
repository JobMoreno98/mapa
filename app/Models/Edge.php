<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Edge extends Model
{
    protected $table = 'edges';

    public $timestamps = false;

    protected $fillable = ['nombre','from_node', 'to_node', 'weight'];
    
    public function fromNode()
    {
        return $this->belongsTo(Nodo::class, 'from_node', 'id');
    }

    public function toNode()
    {
        return $this->belongsTo(Nodo::class, 'to_node', 'id');
    }
}
