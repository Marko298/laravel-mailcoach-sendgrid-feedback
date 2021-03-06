<?php

namespace Spatie\MailcoachSendgridFeedback;

use Illuminate\Support\Arr;
use Spatie\Mailcoach\Models\Send;
use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\ProcessWebhookJob;

class ProcessSendgridWebhookJob extends ProcessWebhookJob
{
    public function __construct(WebhookCall $webhookCall)
    {
        parent::__construct($webhookCall);

        $this->queue = config('mailcoach.perform_on_queue.process_feedback_job');
    }

    public function handle()
    {
        $payload = $this->webhookCall->payload;

        foreach ($payload as $rawEvent) {
            $this->handleRawEvent($rawEvent);
        }
    }

    protected function handleRawEvent(array $rawEvent)
    {
        if (!$send = $this->getSend($rawEvent)) {
            return;
        };

        $sendgridEvent = SendgridEventFactory::createForPayload($rawEvent);

        $sendgridEvent->handle($send);
    }

    protected function getSend(array $rawEvent): ?Send
    {
        $sendUuid = Arr::get($rawEvent, 'send_uuid');

        return Send::findByUuid($sendUuid);
    }
}
