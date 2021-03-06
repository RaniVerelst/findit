<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class CompaniesController extends Controller
{

    public function index(){
        $data['companies'] = \DB::table('companies')->get();
        return view('companies/index', $data);
    }

    public function show($company){
        $data['company'] = \App\Models\Company::where('id', $company)->with('internships')->first();
        
        /*--------------------------------Guzzle-API------------------------------------*/
        //get the current company address
        $companyAddress = $data['company']['address'];
        $companycity = $data['company']['city'];
        //get the API key from the .env file
        $localiq_Api_Key=env('LOCATIONIQ_API_KEY');
        //API request url
        $url_Locationiq = "https://api.locationiq.com/v1/autocomplete.php?key=$localiq_Api_Key&q=$companyAddress";

        // get the Long & Lat of address
        $getLocation = Http::get($url_Locationiq)->json();
        $lat =$getLocation['0']['lat'];
        $lon =$getLocation['0']['lon'];

        //get api key from the .env file
        $here_Api_Key = env('HERE_API_KEY');
        $url_Hereapi ="https://discover.search.hereapi.com/v1/discover?at=$lat,$lon&cat=railway-station&q=railway+station&limit=1&language=fr&apiKey=$here_Api_Key";

        $get_stations=Http::get($url_Hereapi)->json();


        $stations['stations']= $get_stations['items'];
        //dd($stations);
        /*-------------------------------End-Guzzle-API------------------------------------*/
         $url_mobiscore ="https://mobiscore.omgeving.vlaanderen.be/ajax/get-score?lat=$lat&lon=$lon";
        $get_score=Http::get($url_mobiscore)->json();

        if($get_score['status'] != "ok"){
            $stations= $get_stations['items'] ;
            $roundedScore = ' ';
            $errormsg = 'No score is available for this address';
            return view('companies/show', $data,compact('stations','errormsg'));
        }else{
        $onlyScore= $get_score['score']['scores']['totaal'];
         $scores= $get_score['score']['scores'];

        $roundedScore = round($onlyScore,1);


        $stations= $get_stations['items'] ;
        //dd($stations);
        return view('companies/show', $data,compact('scores','stations','roundedScore'));
        
        }
        //return view('companies/show', $data,$stations);
    }

    public function create(){
        return view('companies/create');
    }

    public function store(Request $request){
        $name = $request->input('name');
        $city = $request->input('city');
        $email;
        $phone;
        $address = $request->input('address');
        $postal_code = $request->input('postal_code');
        $province;
        $company = new \App\Models\Company();

        
        $apiKey = env('HERE_API_KEY');
        $url = "https://discover.search.hereapi.com/v1/discover?at=51.030136,4.488213&limit=1&q=$name . $city&apiKey=$apiKey";
        $response = Http::get($url)->json();
        
      
        //$response = "";
        $userId = Auth::id();
        $user = \Auth::user();
        if( $user->can('create', $company)){
        if(empty($response['items'])){
            $company->admin_id = $userId;
            $company->logo = null;
            $company->name = $name;
            $company->city = $city;
            $company->website = null;
            $company->email = null;
            $company->phone = null;
            $company->address = $address;
            $company->postal_code = $postal_code;
            $company->province = null;
            $company->slogan = null;
            $company->description = null;

            $companyExists = \App\Models\Company::where('name', $name)->where('city', $city)->first();
            if($companyExists != null){
                $request->session()->flash('error', 'Company already exists!');
                return redirect('/companies/create');
            }
            else{
                $company->save();
                $request->session()->flash('message', 'Company created!');
                $data = \App\Models\Company::where('name', $name)->where('city', $city)->first();

                return redirect('/companies/' . $data['id']);
            }



        }
        else{
            $response = $response['items'][0];
            $company->name = $name;
            $company->admin_id = $userId;
            $company->logo = null;

            if(!empty($response['contacts'][0]['www'][0]['value'])){
                $website = $response['contacts'][0]['www'][0]['value'];
            }
            else{
                $website = null;
            }
            $company->website = $website;

            if(!empty($response['contacts'][0]['email'][0]['value'])){
                $email = $response['contacts'][0]['email'][0]['value'];
            }
            else{
                $email = null;
            }
            $company->email = $email;

            if(!empty($response['contacts'][0]['phone'][0]['value'])){
                $phone = $response['contacts'][0]['phone'][0]['value'];
            }
            else{
                $phone = null;
            }
            $company->phone = $phone;

            if(!empty($response['address']['street']) && !empty($response['address']['houseNumber'])){
                $address = $response['address']['street'] . ' ' . $response['address']['houseNumber'];
            }
            else{
                $address = null;
            }
            $company->address = $address;
            $company->city = $city;

            if(!empty($response['address']['postalCode'])){
                $postalCode = $response['address']['postalCode'];
            }
            else{
                $postalCode = null;
            }
            $company->postal_code = $postalCode;

            if(!empty($response['address']['state'])){
                $province = $response['address']['state'];
            }
            else{
                $province = null;
            }
            $company->province = $province;

            $company->slogan = null;
            $company->description = null;

            $companyExists = \App\Models\Company::where('name', $name)->where('city', $city)->first();
            if($companyExists != null){
                $request->session()->flash('error', 'Company already exists!');
                return redirect('/companies/create');
            }
            else{
                $company->save();
                $request->session()->flash('message', 'Company created!');
                $data = \App\Models\Company::where('name', $name)->where('city', $city)->first();

                return redirect('/companies/' . $data['id']);
            }
        }

        }
        else {
            $request->session()->flash('error', 'You do not have the authorization to create a company!');
            return redirect('/companies');
        }

    }

    public function update(Request $request){
        $company_id = $request->input('company_id');
        $name = $request->input('name');
        $slogan = $request->input('slogan');
        $description = $request->input('description');
        $address = $request->input('address');
        $city = $request->input('city');
        $postal_code = $request->input('postal_code');
        $province = $request->input('province');
        $website = $request->input('website');
        $phone = $request->input('phone');
        $email = $request->input('email');
        $switch;
        $company = \App\Models\Company::where('id', $company_id)->first();
        $user = \Auth::user();

        if($user->can('update', $company)){
        if( $request->hasFile('logo')){
            $filename = $request->logo->getClientOriginalName();
            $request->logo->storeAs('companyImages', $filename, 'public');
            $data = \App\Models\Company::where('id', $company_id)->update(['logo' => $filename]);
            return redirect('/companies/' . $company_id);
        }
        if($name != null && $name != ' '){
            $data = \App\Models\Company::where('id', $company_id)->update(['name' => $name]);
            return redirect('/companies/' . $company_id);
        }
        else if($slogan != null && $slogan != ' '){
            $data = \App\Models\Company::where('id', $company_id)->update(['slogan' => $slogan]);
            return redirect('/companies/' . $company_id);
        }
        else if($description != null && $description != ' '){
            $data = \App\Models\Company::where('id', $company_id)->update(['description' => $description]);
            return redirect('/companies/' . $company_id);
        }
        else if($address != null && $address != ' ' &&
        $city != null && $city != ' ' &&
        $postal_code != null && $postal_code != ' ' &&
        $province != null && $province != ' '){
            $data = \App\Models\Company::where('id', $company_id)->update(
            ['address' => $address,
            'city' => $city,
            'postal_code' => $postal_code,
            'province' => $province]);
            return redirect('/companies/' . $company_id);
        }
        else if($website != null && $website != ' '){
            $data = \App\Models\Company::where('id', $company_id)->update(['website' => $website]);
            return redirect('/companies/' . $company_id);
        }
        else if($phone != null && $phone != ' '){
            $data = \App\Models\Company::where('id', $company_id)->update(['phone' => $phone]);
            return redirect('/companies/' . $company_id);
        }
        else if($email != null && $email != ' ' && strrpos($email, '@') != false && (strrpos($email, '.com') != false || strrpos($email, '.be') != false)){
            $data = \App\Models\Company::where('id', $company_id)->update(['email' => $email]);
            return redirect('/companies/' . $company_id);
        }
        else{
            return redirect('/companies/' . $company_id);
        }
        }
        else {
            $request->session()->flash('error', 'You are not authorized to update this company!');
            return redirect('/companies/' . $company_id);
        }


    }

}
