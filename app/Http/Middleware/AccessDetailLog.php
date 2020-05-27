<?php
namespace App\Http\Middleware;

use Log;
use Closure;
use Illuminate\Support\MessageBag;

class AccessDetailLog
{
    protected $startTime = null;

    protected $message = null;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->startTime = microtime(true);

        if (! $this->shouldIgnore($request)) {
            $this->startingLog($request);
        }

        $response = $next($request);

        if (! $this->shouldIgnore($request)) {
            $this->endingLog($response);
        }

        // logging for request detail info
        if ($this->message) {
            $this->loggingMessageBag($this->message);
        }

        // logging for request base info
        $this->spendingTimeLog($request);

        return $response;
    }


    protected function shouldIgnore($request)
    {
        $ignoreList = ['/api/api-status'];
        return in_array($request->path(), $ignoreList);
    }

    protected function startingLog($request)
    {
        $this->message = new MessageBag();
        $this->message->add('Request Url', $request->method().' '.$request->fullUrl());
        $this->message->add('Request Headers', (string)$request->headers);
        $this->message->add('Request Content', $request->getContent());
    }

    protected function endingLog($response)
    {
        $this->message->add('Response Status Code', $response->getStatusCode());
        $this->message->add('Response Headers', (string)$response->headers);
        $this->message->add('Response Content', $response->getContent());
    }

    protected function spendingTimeLog($request)
    {
        if (is_null($this->startTime)) {
            return;
        }
        $endTime = microtime(true);

        Log::info(sprintf("%s %s Spend Time: %f seconds", $request->method(), $request->path(), $endTime - $this->startTime));
    }


    protected function loggingMessageBag(MessageBag $messageBag)
    {
        $lines = '';
        foreach ($messageBag->all('[:key: :message]') as $line) {
            $lines .= $line."\n";
        }
        Log::debug($lines);
    }

}

