<?php

namespace App\Http\Controllers;

use DiscordWebhook\Embed;
use DiscordWebhook\EmbedColor;
use DiscordWebhook\Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class WebhookController extends Controller
{
    public function github(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Hub-Signature-256');

        if (!$this->verifySignature($payload, $signature)) {
            return response('Invalid signature', 403);
        }

        $data = collect(json_decode($payload, true));

        if ($this->processEvent($request->header('X-GitHub-Event'), $data)) {
            return response('OK', 200);
        }

        return response('Not OK', 500);
    }

    private function verifySignature($payload, $signature)
    {
        $secret = config('services.github.webhook_secret');

        return hash_equals('sha256=' . hash_hmac('sha256', $payload, $secret), $signature);
    }

    private function processEvent($event, Collection $data): bool
    {
        return match ($event) {
            'release' => $this->processReleaseEvent($data),
            'ping' => $this->processPingEvent($data),
            default => false,
        };
    }

    private function processReleaseEvent(Collection $data): bool
    {
        $action = $data->get('action');
        if (!in_array($action, ['prereleased', 'releaseds'])) {
            return false;
        }

        $release = $data->get('release');
        $assets = $release['assets'];

        $repo = $data->get('repository');

        $webhooks = config('services.github.webhooks');
        $webhooks = $webhooks[$repo['full_name']] ?? null;
        if (!$webhooks) {
            return false;
        }
        $webhooks = explode(',', $webhooks);

        foreach ($webhooks as $url) {
            $webhook = new Webhook($url);
            $webhook->setUsername('GitHub');
            $webhook->setAvatar('https://github.githubassets.com/images/modules/logos_page/GitHub-Mark.png');
            $embed = (new Embed())
                ->setTitle($repo['name'] . ' ' . $release['tag_name'])
                ->setDescription($release['body'])
                ->setUrl($release['html_url'])
                ->setColor(EmbedColor::GREEN)
                ->setTimestamp(new \DateTime($release['published_at']));

            if (!empty($assets)) {
                $embed->addField((new Embed\Field())
                    ->setName('Artifacts')
                    ->setValue(implode("\n", array_map(fn($asset) => "[{$asset['name']}]({$asset['browser_download_url']})", $assets))));
            }

            $webhook->addEmbed($embed);
            $webhook->send();
        }

        return true;
    }

    private function processPingEvent(Collection $data): bool
    {
        return true;
    }

}
