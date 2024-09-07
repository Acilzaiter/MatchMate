

<?php
use Symfony\Component\Notifier\Notification\Notification;

class EmailNotification extends Notification
{
    private $content;

    public function __construct(string $subject, string $content)
    {
        parent::__construct($subject);
        $this->content = $content;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}


