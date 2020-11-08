<?php

declare(strict_types=1);

namespace App\Controllers;

use Exception;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\UploadedFileInterface as UploadedFile;
use App\Models\Message;
use Swift_Message;

class MessageController extends Controller
{
    /**
     * Show the specified message.
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function show(Request $request, Response $response, array $args)
    {
        ['uuid' => $uuid] = $args;

        if ($message = Message::where('uuid', $uuid)->firstOrFail()) {
            $message->delete();
        }

        return $this->render($response, 'messages/show.twig', [
            'message' => $message,
        ]);
    }

    /**
     * Store a message.
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws Exception
     */
    public function store(Request $request, Response $response)
    {
        $data = $this->validate($request, [
            'email'   => ['required', 'email'],
            'message' => ['required'],
        ]);

        $file = $request->getUploadedFiles()['file'];

        if ($file->getError() === UPLOAD_ERR_OK) {
            $file = $this->validateFile($file, [
                'file.type' => [
                    ['in', ['image/jpeg', 'image/png'], 'message' => 'The file must be a type of JPG/JPEG or PNG.'],
                ],
                'file.size' => [
                    ['max', 1000000, 'message' => 'The file size must be less than or equal 1 MB.'],
                ],
            ]);

            $data['file'] = $this->uploadFile($_SERVER['DOCUMENT_ROOT'] . '/uploads', $file);
        }

        $message = Message::create(Arr::only($data, ['message', 'file']));

        $this->sendEmail($data['email'], $message);

        $this->flash('success', 'Message has been sent.');

        return $response->withHeader('Location', '/');
    }

    /**
     * Upload a file.
     *
     * @param string $directory
     * @param UploadedFile $file
     * @return string
     * @throws Exception
     */
    protected function uploadFile(string $directory, UploadedFile $file)
    {
        $extension = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);

        $basename = bin2hex(random_bytes(8));
        $filename = sprintf('%s.%0.8s', $basename, $extension);

        $file->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

        return $filename;
    }

    /**
     * Send an email.
     *
     * @param string $email
     * @param Message $message
     * @return mixed
     */
    protected function sendEmail(string $email, Message $message)
    {
        $mail = (new Swift_Message('A new self destructing message'))
            ->setFrom([
                $this->container->get('config')['mail']['fromAddress'] => $this->container->get('config')['mail']['fromName']
            ])
            ->setTo($email)
            ->setBody(
                $this->container->get('view')->fetch('emails/message.twig', ['message' => $message]),
                'text/html'
            );

        return $this->container->get('mailer')->send($mail);
    }
}
