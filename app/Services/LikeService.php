<?php

namespace App\Services;

use App\Models\Vote;
use App\Models\VotesAggregate;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\ServiceProvider;

class LikeService
{
    public function processVote($entityId, $entityType, $userId, $voteType)
    {
        $oldVote = Vote::where('entity_id', $entityId)
            ->where('entity_type', $entityType)
            ->where('user_id', $userId)
            ->first();

        if (!$oldVote) {
            Vote::create([
                'entity_id'   => $entityId,
                'entity_type' => $entityType,
                'user_id'     => $userId,
                'vote_type'   => $voteType,
            ]);

            if ($voteType === 1) {
                $this->incrementLikeCount($entityType, $entityId);
            } else {
                $this->incrementDislikeCount($entityType, $entityId);
            }
        } else {
            if ($oldVote->vote_type !== $voteType) {
                $oldVote->vote_type = $voteType;
                $oldVote->save();

                if ($oldVote->getOriginal('vote_type') === 1) {
                    $this->decrementLikeCount($entityType, $entityId);
                } else {
                    $this->decrementDislikeCount($entityType, $entityId);
                }

                if ($voteType === 1) {
                    $this->incrementLikeCount($entityType, $entityId);
                } else {
                    $this->incrementDislikeCount($entityType, $entityId);
                }
            }
        }
    }

    public function getCounts($entityId, $entityType)
    {
        $likesKey    = $this->getLikesKey($entityType, $entityId);
        $dislikesKey = $this->getDislikesKey($entityType, $entityId);

        $likes    = Redis::get($likesKey);
        $dislikes = Redis::get($dislikesKey);

        if (is_null($likes) || is_null($dislikes)) {
            $aggregate = VotesAggregate::where('entity_id', $entityId)
                ->where('entity_type', $entityType)
                ->first();

            if ($aggregate) {
                $likes    = $aggregate->likes_count;
                $dislikes = $aggregate->dislikes_count;
            } else {
                $result = Vote::selectRaw("
                    SUM(CASE WHEN vote_type = 1 THEN 1 ELSE 0 END) as likes,
                    SUM(CASE WHEN vote_type = -1 THEN 1 ELSE 0 END) as dislikes
                ")
                ->where('entity_id', $entityId)
                ->where('entity_type', $entityType)
                ->first();

                $likes    = $result->likes ?? 0;
                $dislikes = $result->dislikes ?? 0;
            }

            Redis::set($likesKey, $likes);
            Redis::set($dislikesKey, $dislikes);
        }

        return [
            'likes'    => (int)$likes,
            'dislikes' => (int)$dislikes,
        ];
    }

    // Методы работы с Redis-счётчиками

    protected function incrementLikeCount($entityType, $entityId)
    {
        Redis::incr($this->getLikesKey($entityType, $entityId));
    }

    protected function decrementLikeCount($entityType, $entityId)
    {
        Redis::decr($this->getLikesKey($entityType, $entityId));
    }

    protected function incrementDislikeCount($entityType, $entityId)
    {
        Redis::incr($this->getDislikesKey($entityType, $entityId));
    }

    protected function decrementDislikeCount($entityType, $entityId)
    {
        Redis::decr($this->getDislikesKey($entityType, $entityId));
    }

    protected function getLikesKey($entityType, $entityId)
    {
        return "like_count:{$entityType}:{$entityId}";
    }

    protected function getDislikesKey($entityType, $entityId)
    {
        return "dislike_count:{$entityType}:{$entityId}";
    }
}
