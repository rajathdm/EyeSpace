<?php

namespace App\Http\Controllers;

use App\GenericFile;
use App\Theme;
use App\FieldControl;
use App\FieldDataImage;
use App\FieldDataText;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use File;
use App\Content;
use Log;

trait SpaceTrait {


    /**
     * Prepare space content for theme templates.
     *
     * @param Space $space The space.
     * @param ContentType $contentType 
     * @param bool $preview True if preview.
     *
     * @return String $vars
     */
    private function prepare_space_content($space, $contentType, $preview = false) {

        try {
            $theme = Theme::where('id', $space->theme_id)->where('status', Theme::STATUS_ACTIVE)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            abort(404);
        }

        $vars = [
            'space_url' => url($space->uri) . (($preview==false)?'':'/preview'),
            'space_title' => $space->title,
            'theme_dir' => $theme->root_dir,
            'content' => []
        ];


        $content_all = Content::where('space_id', $space->id)->orderBy('weight', 'asc')->get();

        foreach ($content_all as $content) {

            $vars['content'][$content->key][] = $contentType->loadContent($content->id);
        }

        view()->addNamespace('theme', base_path($theme->root_dir . '/' . Theme::TEMPLATES_DIR));

        return $vars;
    }


    /**
     * Prepare space content for theme templates in JSON format.
     *
     * @param Request $request The request.
     * @param Space $space The space.
     * @param ContentType $contentType 
     * @param string $contenttype_key
     *
     * @return response string
     */
    private function prepare_space_content_json($request, $space, $contentType, $contenttype_key) {

        $response = [];

        $query_params = $request->all();

        if (array_has($query_params, 'per-page')) {
            $content_all = Content::where('space_id', $space->id)->where('key', $contenttype_key)->orderBy('weight', 'asc')->paginate(abs($query_params['per-page']));
        } else {
            $content_all = Content::where('space_id', $space->id)->where('key', $contenttype_key)->orderBy('weight', 'asc')->get();
        }

        foreach ($content_all as $content) {
            $response[$content->key][] = $contentType->loadContentJson($content->id);
        }


        if (array_has($query_params, 'per-page')) {

            $response['total'] = $content_all->total();
            $response['per_page'] = $content_all->count();
            $response['current_page'] = $content_all->currentPage();
            $response['last_page'] = $content_all->lastPage();
            if ($content_all->hasMorePages()) {
                $response['next_page_url'] = $content_all->nextPageUrl() . '&per-page=' . abs($query_params['per-page']);
            } else {
                $response['next_page_url'] = null;
            }
            if ($content_all->currentPage() > 1) {
                $response['prev_page_url'] = $content_all->previousPageUrl() . '&per-page=' . abs($query_params['per-page']);
            } else {
                $response['prev_page_url'] = null;
            }
            $response['from'] = $content_all->firstItem();
            if ($content_all->hasMorePages()) {
                $response['to'] = $content_all->lastItem();
            } else {
                $response['to'] = $content_all->total();
            }
        }

        return $response;
    }

 



    /**
     * Get file URI with suffix. Preserves the file extension.
     *
     * @param String $uri The file URI.
     * @param String $suffix The suffix to append to file URI.
     *
     * @return New file URI with suffix and file extension.
     */
    private function get_file_uri($uri, $suffix) {
        return substr($uri, 0, strrpos($uri, '.')) . $suffix . substr($uri, strrpos($uri, '.'));
    }

}

