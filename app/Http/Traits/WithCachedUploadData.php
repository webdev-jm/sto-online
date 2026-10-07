<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Locked;

/**
 * Keeps a Livewire upload component's parsed rows in the cache instead of the
 * component snapshot.
 *
 * Every request posts the snapshot back to the server, so a large upload held
 * in public properties exceeded the web server's request body limit (413) as
 * soon as the preview was paged. A component lists the properties to keep
 * server-side in cachedUploadData() and declares them protected; they are
 * restored before each request, saved after it when they changed, and handed
 * to the view under their own names.
 */
trait WithCachedUploadData
{
    #[Locked]
    public ?string $uploadDataKey = null;

    private ?string $uploadDataFingerprint = null;

    /**
     * Names of the protected properties that hold the uploaded data.
     *
     * @return array<int, string>
     */
    abstract protected function cachedUploadData(): array;

    /**
     * Seconds an uploaded preview stays available.
     */
    protected function cachedUploadDataTtl(): int
    {
        return 60 * 60 * 6;
    }

    public function bootWithCachedUploadData(): void
    {
        $this->uploadDataFingerprint = $this->fingerprintUploadData();
    }

    public function hydrateWithCachedUploadData(): void
    {
        if ($this->uploadDataKey === null) {
            return;
        }

        $stored = Cache::get($this->uploadDataCacheKey(), []);

        foreach ($this->cachedUploadData() as $property) {
            if (array_key_exists($property, $stored)) {
                $this->{$property} = $stored[$property];
            }
        }

        $this->uploadDataFingerprint = $this->fingerprintUploadData();
    }

    public function dehydrateWithCachedUploadData(): void
    {
        if ($this->fingerprintUploadData() === $this->uploadDataFingerprint) {
            return;
        }

        $this->uploadDataKey ??= (string) Str::uuid();

        Cache::put($this->uploadDataCacheKey(), $this->currentUploadData(), $this->cachedUploadDataTtl());
    }

    public function renderingWithCachedUploadData(View $view): void
    {
        $view->with($this->currentUploadData());
    }

    /**
     * Livewire's own reset() only reaches public properties, so the cached
     * ones are restored to their defaults here and the rest are passed on.
     */
    public function reset(...$properties)
    {
        $properties = count($properties) && is_array($properties[0])
            ? $properties[0]
            : $properties;

        $cached     = $this->cachedUploadData();
        $resetsAll  = empty($properties);
        $ownToReset = $resetsAll ? $cached : array_intersect($properties, $cached);

        if (!empty($ownToReset)) {
            $fresh = new static;

            foreach ($ownToReset as $property) {
                $this->{$property} = $fresh->{$property};
            }
        }

        $others = array_values(array_diff($properties, $cached));

        if ($resetsAll || !empty($others)) {
            parent::reset($others);
        }
    }

    private function uploadDataCacheKey(): string
    {
        return 'upload-data:' . static::class . ':' . $this->uploadDataKey;
    }

    /**
     * @return array<string, mixed>
     */
    private function currentUploadData(): array
    {
        $data = [];

        foreach ($this->cachedUploadData() as $property) {
            $data[$property] = $this->{$property};
        }

        return $data;
    }

    private function fingerprintUploadData(): string
    {
        return md5(serialize($this->currentUploadData()));
    }
}
