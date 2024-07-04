<?php

namespace App\Http\Controllers;

use App\Models\UploadTemplate;
use Illuminate\Http\Request;

class UploadTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages.upload-templates.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UploadTemplate  $uploadTemplate
     * @return \Illuminate\Http\Response
     */
    public function show(UploadTemplate $uploadTemplate)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UploadTemplate  $uploadTemplate
     * @return \Illuminate\Http\Response
     */
    public function edit(UploadTemplate $uploadTemplate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UploadTemplate  $uploadTemplate
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UploadTemplate $uploadTemplate)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UploadTemplate  $uploadTemplate
     * @return \Illuminate\Http\Response
     */
    public function destroy(UploadTemplate $uploadTemplate)
    {
        //
    }
}
