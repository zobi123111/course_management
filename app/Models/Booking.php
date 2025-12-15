<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'resource', 'start', 'end', 'booking_type', 'resource_type', 'instructor_id', 'status','ou_id','group_id','std_id'
    ]; 


    public function resources()
    {
        return $this->belongsTo(Resource::class,'resource', 'id'); 
    }

    public function users()
    {
        return $this->belongsTo(User::class,'std_id', 'id'); 
    }
}
