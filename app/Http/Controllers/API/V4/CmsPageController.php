<?php

namespace App\Http\Controllers\API\V4;

use App\Models\CmsPage;
use Illuminate\Http\Request;
use Validator;

class CmsPageController extends BaseController
{
    public function getPageDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page_key' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $cmsPage = CmsPage::select(['name', 'key', 'content'])->where('key', $request->page_key)->first();

        if ($cmsPage) {
            return $this->sendResponse($cmsPage, 'Page detail retrieved successfully.');
        } else {
            return $this->sendError('Invalid page key', ['error' => 'Page not found']);
        }
    }
    public function getPages()
    {
        $pages = CmsPage::select('name', 'key')->get()->map(function ($page) {
          
            if($page->key == 'privacy-policy'){
              $page->url = "https://www.wellobit.com/privacy";
            }
            elseif($page->key == 'terms-conditions'){
              $page->url = "https://www.notion.so/Terms-of-Use-23963fb191d0808fa320fb7866d35324";
            }
            else{
              $page->url = route('cms.public', ['page' => $page->key]);
            }
            return $page;
        });
        return $this->sendResponse($pages, 'Page detail retrieved successfully.');
    }
}
