<?php

namespace BrightMoon\Session;

use SessionHandlerInterface;

class FileSessionHandler implements SessionHandlerInterface
{
    /**
     * Create a new file driven handler instance.
     *
     * @param  mixed  $files
     * @param  string  $path
     * @param  int  $lifetime
     * @return void
     */
    public function __construct(
        protected $files,
        protected string $path,
        protected int $lifetime
    ) {}

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $sessionId): string|false
    {
        if (file_exists($this->path.'/'.$sessionId)) {
            //
        }

        return '';
    }

    public function write(string $id, string $data): bool
    {
        return true;
    }

    public function destroy(string $id): bool
    {
        return true;
    }

    public function gc(int $lifetime): int|false
    {
        if (file_exists($this->files)) {}

        return false;
    }
}
