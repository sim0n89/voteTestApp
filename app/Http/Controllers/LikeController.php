<?php

namespace App\Http\Controllers;

use App\Services\LikeService;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    protected $likeService;

    public function __construct(LikeService $likeService)
    {
        $this->likeService = $likeService;
    }

    /**
     * POST /api/v1/like
     * Ожидает параметры: entity_id, entity_type, user_id, vote_type (1 или -1)
     */
    public function vote(Request $request)
    {
        $request->validate([
            'entity_id'   => 'required|integer',
            'entity_type' => 'required|string',
            'user_id'     => 'required|integer',
            'vote_type'   => 'required|in:1,-1',
        ]);

        $entityId   = $request->input('entity_id');
        $entityType = $request->input('entity_type');
        $userId     = $request->input('user_id');
        $voteType   = $request->input('vote_type');

        try {
            $this->likeService->processVote($entityId, $entityType, $userId, $voteType);
            return response()->json([
                'status'  => 'success',
                'message' => 'Vote processed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * GET /api/v1/likes-count?entity_id=xxx&entity_type=yyy
     */
    public function getVotesCount(Request $request)
    {
        $request->validate([
            'entity_id'   => 'required|integer',
            'entity_type' => 'required|string',
        ]);

        $entityId   = $request->input('entity_id');
        $entityType = $request->input('entity_type');

        $counts = $this->likeService->getCounts($entityId, $entityType);

        return response()->json([
            'status' => 'success',
            'data'   => $counts
        ]);
    }
}
