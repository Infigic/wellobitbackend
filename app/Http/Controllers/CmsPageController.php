<?php

namespace App\Http\Controllers;

use App\Models\CmsPage;
use Illuminate\Http\Request;

class CmsPageController extends Controller
{
    public function index()
    {
        $pages = CmsPage::paginate(10);

        return view('cms_pages.index', compact('pages'));
    }

    public function create()
    {
        return view('cms_pages.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'key' => 'required|string|max:255|unique:cms_pages,key',
            'content' => 'required|string',
        ]);

        CmsPage::create($request->all());

        return redirect()->route('cms-pages.index')
            ->with('success', 'CMS Page created successfully.');
    }

    public function show(CmsPage $cms_page)
    {
        return view('cms_pages.show', compact('cms_page'));
    }

    public function edit(CmsPage $cms_page)
    {
        return view('cms_pages.edit', compact('cms_page'));
    }

    public function update(Request $request, CmsPage $cms_page)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'key' => 'required|string|max:255|unique:cms_pages,key,' . $cms_page->id,
            'content' => 'required|string',
        ]);

        $cms_page->update($request->all());

        return redirect()->route('cms-pages.index')
            ->with('success', 'CMS Page updated successfully');
    }

    public function destroy(CmsPage $cms_page)
    {
        $cms_page->delete();

        return redirect()->route('cms-pages.index')
            ->with('success', 'CMS Page deleted successfully');
    }

    public function getPageContent($page)
    {
        $page = CmsPage::where('key', $page)->first();

        if (! $page) {
            abort(404, 'Page not found');
        }
        return view('cms', compact('page'));
    }
}
