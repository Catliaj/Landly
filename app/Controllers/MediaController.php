<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class MediaController extends BaseController
{
    public function profile(): ResponseInterface
    {
        $rawPath = (string) ($this->request->getGet('path') ?? '');
        $rawPath = trim($rawPath);

        if ($rawPath === '') {
            return $this->response->setStatusCode(404)->setBody('Image not found.');
        }

        $normalizedPath = str_replace('\\', '/', $rawPath);
        $normalizedPath = preg_replace('#^/?uploads/#i', '', $normalizedPath) ?? $normalizedPath;
        $normalizedPath = ltrim($normalizedPath, '/');

        // Prevent path traversal and only allow profile pictures folder.
        if (str_contains($normalizedPath, '..') || ! str_starts_with($normalizedPath, 'profile_pictures/')) {
            return $this->response->setStatusCode(404)->setBody('Image not found.');
        }

        $fullPath = WRITEPATH . 'uploads/' . $normalizedPath;
        if (! is_file($fullPath)) {
            return $this->response->setStatusCode(404)->setBody('Image not found.');
        }

        $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';
        $contents = file_get_contents($fullPath);
        if ($contents === false) {
            return $this->response->setStatusCode(500)->setBody('Unable to load image.');
        }

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Cache-Control', 'public, max-age=86400')
            ->setBody($contents);
    }
}
