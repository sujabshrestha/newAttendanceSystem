<?php

use App\Model\MemberOrganizationInfo;
use CMS\Models\PointInfo;
use SiteSetting\Models\SiteSetting;
use Files\Models\UploadFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Membership\Models\UserPointReduction;
use PublicOpinion\Models\PublicOpinionVote;
use User\Models\UserInfo;
use Vendor\Models\Booking;

function seperator($depth)
{
    $space = '';
    for ($i = 1; $i < $depth; $i++) {
        $space .= '-';
    }
    return $space;
}


function getdeliverydetails($userid = null)
{
    if ($userid != null) {
        $userinfo = UserInfo::where('user_id', $userid)->pluck('value', 'key')->toArray();
        if ($userinfo != null) {
            return $userinfo;
        }
        return false;
    }
    return false;
}


function getSiteSetting( $key ) {
    $config = SiteSetting::where( 'key', '=', $key )->first();
    if ( $config != null ) {
        return $config->value;
    }
    return null;
}


function totalVoteUsers(){
    $totalvoteusers = PublicOpinionVote::groupBy('user_id')->count();
    return $totalvoteusers;
}


function returnUserDetail($key = null, $userid = null)
{
    if ($key != null && $userid != null) {
        $userinfo = UserInfo::where('user_id', $userid)->where('key', $key)->first();
        if ($userinfo) {

            return $userinfo->value;
        }
    }
    return null;
}


function returnSiteSetting($key = null)
{
    if ($key != null) {
        $sitesetting = SiteSetting::where('key', $key)->first();
        if ($sitesetting) {

            return $sitesetting->value;
        }
    }
    return null;
}


function vendorBookingApprovals(){
    if(Auth::check()){
        $bookings = Booking::where('vendor_id', Auth::id())
        ->with(['venues', 'applications'])->latest()->get();
        if(!is_null($bookings)){
            return $bookings;
        }
        return false;
    }
    return false;
}



function ProductRating($rating)
{
    if ($rating->count() > 0) {
        return $rating->sum('rating') / $rating->count();
    } else {
        return 0;
    }
}

function thumbnail_url($file)
{
    $supportExtension = array('jpg', 'png', 'gif', 'webp');
    if (in_array($file->extension, $supportExtension)) {
        return Storage::url('resize/' . $file->path);
    } else {
        return Storage::url($file->path);
    }

    return null;
}

function getThumbnailUrl($id)
{
    $file = UploadFile::where('id', $id)->first();
    if ($file) {
        $supportExtension = array('jpg', 'png', 'gif', 'webp');
        if (in_array($file->extension, $supportExtension)) {
            return Storage::url('resize/' . $file->path);
        } else {
            return Storage::url($file->path);
        }
    }
    return null;
}


function getOrginalUrl($id)
{
    $file = UploadFile::where('id', $id)->first();
    if ($file) {
        return Storage::url($file->path);
    }
    return null;
}


function getFileTitle($id)
{
    $file = UploadFile::where('id', $id)->first();
    if ($file) {
        return $file->title;
    }
    return null;
}

function getFilePath($id)
{
    $file = UploadFile::where('id', $id)->first();
    if ($file) {
        return $file->path;
    }
    return null;
}

function original_url($file)
{
    $supportExtension = array('jpg', 'png', 'gif', 'webp');
    if (in_array($file->extension, $supportExtension)) {
        return Storage::url($file->path);
    } else {
        return Storage::url($file->path);
    }

    return null;
}


function returnImage($image, $path)
{
    if (File::exists($path)) {
        File::delete($path);
    }
    $requestedimage = $image;
    $imagename = time() . str_replace(" ", "", $requestedimage->GetClientOriginalName());
    $path = public_path('image/product');

    $requestedimage->move($path, $imagename);
    return 'image/product/' . $imagename;
}

function returnBrandBanner($image, $path)
{

    if (File::exists($path)) {
        File::delete($path);
    }
    $requestedimage = $image;
    $imagename = time() . str_replace(" ", "", $requestedimage->GetClientOriginalName());
    $path = public_path('image/brand');

    $requestedimage->move($path, $imagename);
    return 'image/brand/' . $imagename;
}
function returnCategoryBanner($image, $path)
{

    if (File::exists($path)) {
        File::delete($path);
    }
    $requestedimage = $image;
    $imagename = time() . str_replace(" ", "", $requestedimage->GetClientOriginalName());
    $path = public_path('image/category/banner');
    $requestedimage->move($path, $imagename);
    return 'image/category/banner/' . $imagename;
}
function returnCategoryLogo($image, $path)
{

    if (File::exists($path)) {
        File::delete($path);
    }
    $requestedimage = $image;
    $imagename = time() . str_replace(" ", "", $requestedimage->GetClientOriginalName());
    $path = public_path('image/category/logo');

    $requestedimage->move($path, $imagename);
    return 'image/category/logo/' . $imagename;
}




function returnOrganizationMemberInfo($memberid, $key)
{
    $memberorg_info = MemberOrganizationInfo::where('member_org_id', $memberid)->where('key', $key)->first();
    if ($memberorg_info) {
        return $memberorg_info->value;
    }
    return null;
}



function getFileUrlByUploads($upload = null, $type = null)
{
    $file = $upload;
    if ($file != null) {

        if ($type == "small") {
            $supportExtension = array('jpg', 'png', 'gif', 'webp');
            if (in_array($file->extension, $supportExtension)) {
                return Storage::url('resize/' . $file->path);
            } else {
                return Storage::url($file->path);
            }
        } else {
            return Storage::url($file->path);
        }
    }
    return null;
}

function checkFileExists($id = null){
    $uploadfile = UploadFile::where('id', $id)->first();
    if($uploadfile){
        if(Storage::exists($uploadfile->path)){
            return true;
        }
        return false;
    }
    return false;
}



function checkMemberReduction($propertydetailid = null)
{
    $memberreduction  = UserPointReduction::where('user_id', Auth::id())
        ->where('property_detail_id', $propertydetailid)
        ->first();
    if ($memberreduction) {
        return true;
    }
    return false;
}


function getMemberViewPoint()
{

    $viewPoint = cache()->remember('member-view-point', 60 * 60, function () {

        $pointsInfo = PointInfo::where('role', 'member')->where('type', 'view')->first();
        if($pointsInfo){
           return  $pointsInfo->point;
        }else{
            return PointInfo::DEFAULT_VIEW_POINT;
        }

    });

    return $viewPoint;
}





