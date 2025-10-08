<?php

declare(strict_types=1);

namespace MasyukAI\Jnt\Data;

class TrackingDetailData
{
    public function __construct(
        public readonly string $scanTime,
        public readonly string $desc,
        public readonly string $scanTypeCode,
        public readonly string $scanTypeName,
        public readonly string $scanType,
        public readonly ?string $realWeight = null,
        public readonly ?string $scanNetworkTypeName = null,
        public readonly ?string $scanNetworkName = null,
        public readonly ?string $staffName = null,
        public readonly ?string $staffContact = null,
        public readonly ?string $scanNetworkContact = null,
        public readonly ?string $scanNetworkProvince = null,
        public readonly ?string $scanNetworkCity = null,
        public readonly ?string $scanNetworkArea = null,
        public readonly ?string $sigPicUrl = null,
        public readonly ?string $longitude = null,
        public readonly ?string $latitude = null,
        public readonly ?string $timeZone = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            scanTime: $data['scanTime'],
            desc: $data['desc'],
            scanTypeCode: $data['scanTypeCode'],
            scanTypeName: $data['scanTypeName'],
            scanType: $data['scanType'],
            realWeight: $data['realWeight'] ?? null,
            scanNetworkTypeName: $data['scanNetworkTypeName'] ?? null,
            scanNetworkName: $data['scanNetworkName'] ?? null,
            staffName: $data['staffName'] ?? null,
            staffContact: $data['staffContact'] ?? null,
            scanNetworkContact: $data['scanNetworkContact'] ?? null,
            scanNetworkProvince: $data['scanNetworkProvince'] ?? null,
            scanNetworkCity: $data['scanNetworkCity'] ?? null,
            scanNetworkArea: $data['scanNetworkArea'] ?? null,
            sigPicUrl: $data['sigPicUrl'] ?? null,
            longitude: $data['longitude'] ?? null,
            latitude: $data['latitude'] ?? null,
            timeZone: $data['timeZone'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'scanTime' => $this->scanTime,
            'desc' => $this->desc,
            'scanTypeCode' => $this->scanTypeCode,
            'scanTypeName' => $this->scanTypeName,
            'scanType' => $this->scanType,
            'realWeight' => $this->realWeight,
            'scanNetworkTypeName' => $this->scanNetworkTypeName,
            'scanNetworkName' => $this->scanNetworkName,
            'staffName' => $this->staffName,
            'staffContact' => $this->staffContact,
            'scanNetworkContact' => $this->scanNetworkContact,
            'scanNetworkProvince' => $this->scanNetworkProvince,
            'scanNetworkCity' => $this->scanNetworkCity,
            'scanNetworkArea' => $this->scanNetworkArea,
            'sigPicUrl' => $this->sigPicUrl,
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,
            'timeZone' => $this->timeZone,
        ], fn ($value) => $value !== null);
    }
}
