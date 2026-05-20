<?php

namespace App\Services;

use App\Models\CosmeticAsset;
use App\Models\CosmeticVote;
use App\Models\User;

class CosmeticAssetService
{
    public function vote(User $user, string $sha, int $vote): void
    {
        $asset = CosmeticAsset::bySha($sha)->firstOrFail();

        if ($asset->uploader_id === $user->id) {
            throw new \InvalidArgumentException('Cannot vote on your own cosmetic');
        }

        CosmeticVote::updateOrCreate(
            ['cosmetic_id' => $asset->id, 'user_id' => $user->id],
            ['vote' => $vote]
        );
    }

    public function unvote(User $user, string $sha): void
    {
        $asset = CosmeticAsset::bySha($sha)->firstOrFail();
        CosmeticVote::where('cosmetic_id', $asset->id)->where('user_id', $user->id)->delete();
    }

    public function submitEdit(User $user, string $sha, array $data): void
    {
        $asset = CosmeticAsset::bySha($sha)->firstOrFail();

        if ($asset->uploader_id !== $user->id) {
            throw new \InvalidArgumentException('Only the uploader can edit this cosmetic');
        }

        if ($asset->hasPendingEdit()) {
            throw new \RuntimeException('A pending edit already exists');
        }

        $patch = [];
        if (array_key_exists('name', $data)) {
            $patch['pending_name'] = $data['name'];
        }
        if (array_key_exists('visibility', $data)) {
            $patch['pending_visibility'] = $data['visibility'];
        }
        if (array_key_exists('tags', $data)) {
            $patch['pending_tags'] = array_values(array_unique(array_slice(
                array_map('strtolower', array_filter($data['tags'] ?? [])),
                0,
                10
            )));
        }

        $asset->update($patch);
    }

    public function approveEdit(string $sha): void
    {
        $asset = CosmeticAsset::bySha($sha)->firstOrFail();

        $patch = [];
        if ($asset->pending_name !== null) {
            $patch['name'] = $asset->pending_name;
        }
        if ($asset->pending_visibility !== null) {
            $patch['visibility'] = $asset->pending_visibility;
        }
        if ($asset->pending_tags !== null) {
            $patch['tags'] = $asset->pending_tags;
        }

        $patch['pending_name']       = null;
        $patch['pending_visibility'] = null;
        $patch['pending_tags']       = null;

        $asset->update($patch);
    }

    public function rejectEdit(string $sha): void
    {
        CosmeticAsset::bySha($sha)->update([
            'pending_name'       => null,
            'pending_visibility' => null,
            'pending_tags'       => null,
        ]);
    }

    public function incrementEquipCount(string $sha): void
    {
        CosmeticAsset::bySha($sha)->increment('equip_count');
    }

    public function decrementEquipCount(string $sha): void
    {
        CosmeticAsset::bySha($sha)
            ->where('equip_count', '>', 0)
            ->decrement('equip_count');
    }
}
