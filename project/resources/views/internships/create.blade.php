@extends('layouts/app')

@section('title', 'Voeg een stage')

@section('nav')
    <li class="nav-item">
          <a class="nav-link" href="/companies/{{request()->route('company_id')}}">Back</a>
    </li>
@endsection

@section('content')

    <h1>Create new internship</h1>

     <form method="post" action="/internships">

          {{ csrf_field() }}

          <div class="form-group">
          <label for="title">Internship title</label>
          <input type="text" class="form-control" id="title" name="title" placeholder="Your title">
          </div>

          <div class="form-group">
          <label for="bio">Internship description</label>
          <textarea class="form-control" id="bio" name="bio" rows="3" placeholder="Your description"></textarea>
          </div>

         <div class="form-group">
          <label for="type">Internship type</label>
            <select class="btn btn-primary" name="type" id="type">
                <option value="" disabled selected>Choose a type</option>
                <option value="Design">Design</option>
                <option value="Development">Development</option>
            </select>
          </div>

          <div class="form-group">
          <label for="req_skills">Internship required skills</label>
          <textarea class="form-control" id="req_skills" name="req_skills" rows="5" placeholder="Ex.&#10HTML&#10CSS&#10JS&#10PHP"></textarea>
          </div>

          <div class="form-group">
          <label for="start">Internship start date</label>
          <input type="date" class="form-control" id="start" name="start" placeholder="jjjj-mm-dd">
          </div>

          <div class="form-group">
          <label for="end">Internship end date</label>
          <input type="date" class="form-control" id="end" name="end" placeholder="jjjj-mm-dd">
          </div>

          <button type="submit" class="btn btn-primary" name="company_id" value="{{request()->route('company_id')}}">Create internship</button>

     </form>
@endsection