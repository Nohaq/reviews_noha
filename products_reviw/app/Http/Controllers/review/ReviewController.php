<?php

namespace App\Http\Controllers\review;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewCollection;
use App\Http\Resources\ReviewResource;
use App\Http\Traits\GeneralTrait;
use App\Models\Product;
use App\Models\Review;
use Auth;
use Illuminate\Http\Request;
use Validator;

class ReviewController extends Controller
{
    use GeneralTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       try{
           $msg='all reviews are Right Here';
           $data=Review::with('user','product')->get();
           return $this->successResponse(new ReviewCollection($data),$msg);
       }
       catch (\Exception $ex){
           return $this->errorResponse($ex->getMessage(),500);
       }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request,$product)
    {
        $validator=Validator::make($request->all(),[
            'comment'=>'regex:/[a-zA-Z\s]+/',
                'stars'=>'required|numeric',
            ]
        );
                if($validator->fails()){
            return $this->errorResponse($validator->errors(),422);
        }
      try {
           
            $review = Review::create($request->all());
            $review->user()->associate(Auth::user())->save();
            $review->product()->associate(Product::find($product))->save();
           $data=$review;
           $msg='review is created successfully';
            return $this->successResponse(new ReviewResource($data),$msg,201);
        }
        catch (\Exception $ex)
        {
            return $this->errorResponse($ex->getMessage(),500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try{
            $data=Review::with('user','product')->find($id);
            if(!$data)
                return $this->errorResponse('No review with such id',404);
            $msg='Got you the review you are looking for';
            return $this->successResponse(new ReviewResource( $data),$msg);
        }
        catch (\Exception $ex){
            return $this->errorResponse($ex->getMessage(),500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        try{
            $data=Review::find($id);
            if(!$data)
                return $this->errorResponse('No review with such id',404);

            $data->update($request->all());
            $data->save();
            $msg='The review is updated successfully';
            return $this->successResponse(new ReviewResource( $data),$msg);
        }
        catch (\Exception $ex){
            return $this->errorResponse($ex->getMessage(),500);
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try{
            $data=Review::find($id);
            if(!$data)
                return $this->errorResponse('No review with such id',404);

            $data->delete();
            $msg='The review is deleted successfully';
            return $this->successResponse(new ReviewResource( $data),$msg);
        }
        catch (\Exception $ex){
            return $this->errorResponse($ex->getMessage(),500);
        }
    }
    function getAllUserReviews($user){
        try {
            $data= Review::whereRelation('user','user_id','=',$user)->with('user','product')->get();
            $msg='Got data Successfully';
            return $this->successResponse(new ReviewCollection($data),$msg);
        }
    catch (\Exception $ex)
    { return $this->errorResponse($ex->getMessage(),500); }
    
    }
    function getAllProductReviews($product){
        try {
            $data= Review::whereRelation('product','product_id','=',$product)->with('user','product')->get();
            $msg='Got data Successfully';
            return $this->successResponse(new ReviewCollection($data),$msg);
        }
    catch (\Exception $ex)
    { return $this->errorResponse($ex->getMessage(),500); }
    
    }
}
