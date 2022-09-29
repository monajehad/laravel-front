<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Portfolio\SavePortfolioRequest;
use App\Models\Portfolio;
use App\Models\Portfolio_media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortfoliosController extends Controller
{

    public function index(){
    	if (request()->expectsJson()) {

            $portfolio = Portfolio::where('user_id',Auth::user()->id)->with('portfolioMedia')->orderBy('updated_at','desc')
            ->metronicPaginate();
            // dd($portfolio);

    		return response()->json($portfolio)->setStatusCode(200);
        }
        $portfolios = Portfolio::where('user_id',Auth::user()->id)->get();
      //  dd($portfolios);

        if ($portfolios->isEmpty()){
        return view('portfolios.portfolios',get_defined_vars());
     }
         else{
            $portfolioss=Portfolio_media::get()->first();

             //return (get_defined_vars());
         return view('portfolios.portfolios_datatable',get_defined_vars())->with('portfol',$portfolioss);

         }
    }

    public function create(){
        $portfolios = null;
        return view('portfolios.save_portfolios',get_defined_vars());
    }
    public function upload_img(Request $request){
        // Portfolio_media::where('portfolio_id',0)->
        // delete();
    //    dd($request);
        $i=1;
        
        if($request->hasfile('name_media')){
            $files=$request->name_media;
          // dd($files);
            foreach ($files as $file) {
                $name=uniqid(). '-' . now()->timestamp.$file->getClientOriginalName() ;
                $path = $file->storeAs("public/uploads/tmp/" , $name);
                Portfolio_media::create([ 
                    'position'=>$i++,
                    'type'=> "doc",
                    'portfolio_id'=> 0,
                    'name'=> $name,
                ]);
                
            }
        }
    }
    public function store(SavePortfolioRequest $request){
        //dd($request->type);
        
        $portfolio = Portfolio::create(array_merge($request->all(),['user_id' => Auth::user()->id])); // comment
        //$this->upload_img($request);
       
         Portfolio_media::where('portfolio_id',0)->
        update([
            'portfolio_id'=>$portfolio->id,
            'type'=>$request->type

        ]);
        // Portfolio_media::where('portfolio_id',0)->
        // delete();
        return sendResponse(true, trans('add_data'),  $portfolio, 200);
    }
   


    public function edit($id){
        $portfolioss=Portfolio_media::where('portfolio_id',$id)->get()->first();
        $portfoliosimage=Portfolio_media::where('portfolio_id',$id)->get();
       //return $portfolioss['type'];
       $portfolio = Portfolio::findOrFail($id);
      //return $portfolio;
        return view('portfolios.save_portfolios',get_defined_vars())->with('portfolioss',$portfolioss)->with('portfoliosimage',$portfoliosimage); //بترجع كل القيم المعرفة

    }

    public function update(SavePortfolioRequest $request){
        $inputs=$request->all();
        //dd($inputs);
        $portfolio = Portfolio::findOrFail($request->portfolio_id);
        $portfolio->update($inputs);
        
        $portfolio_img=Portfolio_media::where('portfolio_id',$request->portfolio_id)->get();
        $i=0;
        $updated_ids = $request->profile_avatar_remove;
        foreach($portfolio_img as  $img){
            if($img->id!=$updated_ids[$i++]){
                $img->delete();
            }
        }
        Portfolio_media::where('portfolio_id',$request->portfolio_id)->
        update([
            
            'type'=>$request->type,
        ]);

        if($request->name!=null ){
            foreach($request->name as  $key=>$value){
                $img = Portfolio_media::find($key);
                $img->name = uploadImage($value,Portfolio_media::MEDIA_PATH,'400','', $img->name??'');
                $img->save();
            }
        }
        Portfolio_media::where('portfolio_id',0)->
        update([
            'portfolio_id'=>$portfolio->id,
          

        ]);
        $portfolio->save();
       

        return sendResponse(true, trans('update_data'),  $portfolio, 200);
    }


    public function delete(Request $request){
        // $img=Portfolio_media::where('portfolio_id',$request->id)->delete();
        $data = Portfolio::findOrFail($request->id)->delete();

        return sendResponse(true, null, null , 200);
    }

    public function multiDelete(Request $request){
        $data = Portfolio::whereIn('id',$request->id)->delete();
        return sendResponse(true, null, null , 200);
    }



    public function updateStatus(Request $request)
    {
        $data = Portfolio::findOrFail($request->id);
        $data[$request->key] = $request->status ? 1 : 0;
        $data->save();
       return sendResponse(true , "Status changed successfully" , null , 200);
    }
}
