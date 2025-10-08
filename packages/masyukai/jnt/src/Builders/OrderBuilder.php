<?php

declare(strict_types=1);

namespace MasyukAI\Jnt\Builders;

use MasyukAI\Jnt\Data\AddressData;
use MasyukAI\Jnt\Data\ItemData;
use MasyukAI\Jnt\Data\PackageInfoData;
use MasyukAI\Jnt\Exceptions\JntException;

class OrderBuilder
{
    protected string $customerCode;

    protected string $password;

    protected string $txlogisticId;

    protected string $expressType = 'EZ';

    protected string $serviceType = '1';

    protected string $payType = 'PP_PM';

    protected ?AddressData $sender = null;

    protected ?AddressData $receiver = null;

    protected ?AddressData $returnInfo = null;

    /** @var array<ItemData> */
    protected array $items = [];

    protected ?PackageInfoData $packageInfo = null;

    protected ?string $sendStartTime = null;

    protected ?string $sendEndTime = null;

    protected ?string $offerValue = null;

    protected ?string $codValue = null;

    protected ?string $remark = null;

    protected ?array $customsInfo = null;

    protected ?array $multipleVotes = null;

    public function __construct(string $customerCode, string $password)
    {
        $this->customerCode = $customerCode;
        $this->password = $password;
    }

    public function txlogisticId(string $txlogisticId): self
    {
        $this->txlogisticId = $txlogisticId;

        return $this;
    }

    public function expressType(string $expressType): self
    {
        $this->expressType = $expressType;

        return $this;
    }

    public function serviceType(string $serviceType): self
    {
        $this->serviceType = $serviceType;

        return $this;
    }

    public function payType(string $payType): self
    {
        $this->payType = $payType;

        return $this;
    }

    public function sender(AddressData $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    public function receiver(AddressData $receiver): self
    {
        $this->receiver = $receiver;

        return $this;
    }

    public function returnInfo(AddressData $returnInfo): self
    {
        $this->returnInfo = $returnInfo;

        return $this;
    }

    public function addItem(ItemData $item): self
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * @param  array<ItemData>  $items
     */
    public function items(array $items): self
    {
        $this->items = $items;

        return $this;
    }

    public function packageInfo(PackageInfoData $packageInfo): self
    {
        $this->packageInfo = $packageInfo;

        return $this;
    }

    public function sendStartTime(string $sendStartTime): self
    {
        $this->sendStartTime = $sendStartTime;

        return $this;
    }

    public function sendEndTime(string $sendEndTime): self
    {
        $this->sendEndTime = $sendEndTime;

        return $this;
    }

    public function insurance(string $offerValue): self
    {
        $this->offerValue = $offerValue;

        return $this;
    }

    public function cod(string $codValue): self
    {
        $this->codValue = $codValue;

        return $this;
    }

    public function remark(string $remark): self
    {
        $this->remark = $remark;

        return $this;
    }

    public function customsInfo(array $customsInfo): self
    {
        $this->customsInfo = $customsInfo;

        return $this;
    }

    public function multipleVotes(array $multipleVotes): self
    {
        $this->multipleVotes = $multipleVotes;

        return $this;
    }

    public function build(): array
    {
        $this->validate();

        $payload = [
            'customerCode' => $this->customerCode,
            'password' => $this->password,
            'txlogisticId' => $this->txlogisticId,
            'actionType' => 'add',
            'serviceType' => $this->serviceType,
            'payType' => $this->payType,
            'expressType' => $this->expressType,
            'sender' => $this->sender->toArray(),
            'receiver' => $this->receiver->toArray(),
            'items' => array_map(fn (ItemData $item) => $item->toArray(), $this->items),
            'packageInfo' => $this->packageInfo->toArray(),
        ];

        if ($this->returnInfo !== null) {
            $payload['returnInfo'] = $this->returnInfo->toArray();
        }

        if ($this->sendStartTime !== null) {
            $payload['sendStartTime'] = $this->sendStartTime;
        }

        if ($this->sendEndTime !== null) {
            $payload['sendEndTime'] = $this->sendEndTime;
        }

        if ($this->offerValue !== null) {
            $payload['offerFeeInfo'] = ['offerValue' => $this->offerValue];
        }

        if ($this->codValue !== null) {
            $payload['codInfo'] = ['codValue' => $this->codValue];
        }

        if ($this->remark !== null) {
            $payload['remark'] = $this->remark;
        }

        if ($this->customsInfo !== null) {
            $payload['customsInfo'] = $this->customsInfo;
        }

        if ($this->multipleVotes !== null) {
            $payload['multipleVotes'] = $this->multipleVotes;
        }

        return $payload;
    }

    protected function validate(): void
    {
        if (! isset($this->txlogisticId)) {
            throw JntException::invalidConfiguration('txlogisticId is required');
        }

        if ($this->sender === null) {
            throw JntException::invalidConfiguration('Sender address is required');
        }

        if ($this->receiver === null) {
            throw JntException::invalidConfiguration('Receiver address is required');
        }

        if (empty($this->items)) {
            throw JntException::invalidConfiguration('At least one item is required');
        }

        if ($this->packageInfo === null) {
            throw JntException::invalidConfiguration('Package info is required');
        }
    }
}
