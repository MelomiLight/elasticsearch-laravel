<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class MovieController extends Controller
{
    public function search(Request $request): JsonResponse {
        $page = $request->get('page', 1);
        $perPage = $request->get('perPage', 10);
        $query = $request->get('query');

        try {
            $ids = Movie::search($query, [
                'size' => $perPage,
                'from' => ($page - 1) * $perPage,
            ]);
            return response()->json([
                'data' => [
                    'movies' => Movie::getMoviesFromSearch($ids),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json('Unexpected error ' . $e->getMessage(), 500);
        }
    }
}
