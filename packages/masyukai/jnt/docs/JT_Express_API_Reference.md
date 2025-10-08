# J&T Express Malaysia Open API Reference
**Version:** 2025-10-08 · **Language:** English

> This document consolidates the Malaysia tenant of J&T Express Open Platform based on official pages and content you provided.  
> Field names are kept **exactly** as in the API to ensure compatibility. All requests use `application/x-www-form-urlencoded` with a JSON string in `bizContent`.

---

## 1) Introduction
J&T Open Platform exposes APIs to create and manage shipments, print AWBs, and retrieve tracking information. This guide explains the **end‑to‑end integration flow** and provides **testing credentials**, **signature rules**, **examples**, and **Postman** snippets.

### 1.1 Integration Process
1. Register on the J&T Open Platform.
2. Apply to become a developer and complete the required information.
3. Pass **Developer Verification** to obtain API permissions (Order Service, E‑Waybill, etc.).
4. Coordinate with your J&T PIC to perform **Joint‑Debugging** in the **Testing Environment**.
5. After passing, coordinate launch details with your PIC for **Production** enablement.

### 1.2 Environments & Base URLs
- **Testing (Sandbox):** `https://demoopenapi.jtexpress.my/webopenplatformapi`  
- **Production (Official):** `https://ylopenapi.jtexpress.my/webopenplatformapi`

**Testing credentials (for sandbox only):**
```
apiAccount: 640826271705595946
privateKey: 8e88c8477d4e4939859c560192fcafbc
```
> Production `apiAccount`/`privateKey` are available in Console → Personal Center. `customerCode` and the **plain‑text** `password` are issued by your Distribution Partner (DP).

---

## 2) Signature & Security

### 2.1 Header Signature (digest)
Compute `digest` over the **exact** JSON string of `bizContent` **concatenated** with your `privateKey`, then MD5 (binary) → Base64.
```
digest = base64( md5( JSON(bizContent) + privateKey ) )
```
- Ensure the JSON string you sign is exactly the one you send (same ordering/whitespace).
- If signature still fails, URL‑encode the request body before sending (framework dependent).

### 2.2 Business Parameter password
Some endpoints require a **plain‑text** `password` inside `bizContent`. This is **not** the same as `privateKey`. Obtain it from your J&T PIC/DP per environment.

### 2.3 Required Headers
| Header | Type | Required | Notes |
|---|---|---|---|
| `apiAccount` | Number | Yes | From Console |
| `digest` | String | Yes | See 2.1 |
| `timestamp` | Number | Yes | Milliseconds (UTC+8 indicated in UI) |

### 2.4 Field Compliance
Implement strictly per **parameter type and size**; all **required** fields must be supplied on every request.

---

## 3) FAQ Highlights
- **Error `145003030` (headers signature failure):** Verify you are computing `base64(md5(bizContent + privateKey))`. Compare against the **Signature Tool** result.
- **Scan node codes:** `10` Pick Up, `20` Departure, `30` Arrival, `94` Delivery, `100` Delivery Signature, `110` Problematic, `172` Return, `173` Return Delivery Signature, `200` Collected, plus `300–306` terminal statuses (Damaged/Lost/Disposed/Rejected/Customs/Expired/Crossborder Disposal).  
- **Re‑Joint‑Debugging:** You can reinitiate without changing API status in **Console → Application Management**.
- **Multiple apiAccounts:** Use Application Management when different business models require different credentials.

---

## 4) API Specifications

> All endpoints below:
> - **Method:** `POST`  
> - **Content‑Type:** `application/x-www-form-urlencoded`  
> - **Body:** single field `bizContent` containing the business JSON

### 4.1 Create Order — `/api/order/addOrder`
**Testing:** `https://demoopenapi.jtexpress.my/webopenplatformapi/api/order/addOrder`  
**Production:** `https://ylopenapi.jtexpress.my/webopenplatformapi/api/order/addOrder`  

**Headers:** `apiAccount`, `digest`, `timestamp`

**bizContent (top-level)**  
`customerCode` *(String(30), Y)* — e.g., `J0086474299`  
`password` *(String(100), Y)* — business password (plain text)  
`txlogisticId` *(String(50), Y)* — customer order number  
`actionType` *(String(30), Y)* — `"add"`  
`serviceType` *(String(30), Y)* — `1` Door‑to‑door, `6` Walk‑In  
`payType` *(String(30), Y)* — `PP_PM` | `PP_CASH` | `CC_CASH`  
`expressType` *(String(30), Y)* — `EX` Next Day, `EZ` Domestic, `FD` Fresh  
`sender` *(Object, Y)* — **see Sender**  
`receiver` *(Object, Y)* — **see Receiver**  
`returnInfo` *(Object, N)* — return party  
`items` *(List, Y)* — items array (**see Items**)  
`packageInfo` *(Object, Y)* — **see PackageInfo**  
`sendStartTime` *(String(30), N)* — `YYYY-MM-DD HH:mm:ss`  
`sendEndTime` *(String(30), N)* — `YYYY-MM-DD HH:mm:ss`  
`offerFeeInfo` *(Object, N)* — insurance (**see OfferFeeInfo**)  
`codInfo` *(Object, N)* — COD (**see CodInfo**)  
`remark` *(String(200), N)*  
`customsInfo` *(Object, N)* — customs data (**see CustomsInfo**)  
`multipleVotes` *(Array, N)* — multi‑parcel dimensions/weights

**Sender**  
`name`*(Y)*, `phone`*(Y)*, `countryCode`*(Y)* (domestic defaults `MYS`), `address`*(Y)*, `postCode`*(Y)*

**Receiver**  
`name`*(Y)*, `phone`*(Y)*, `countryCode`*(Y)*, `prov`*(N\* for MYS)*, `city`*(N\* for MYS)*, `area`*(N\* for MYS)*, `address`*(Y)*, `postCode`*(Y)*  
> If **international** (non‑`MYS`), `prov/city/area` become **required**; `email` (THA) and `idCard` (CHN/VNM) may be required.

**PackageInfo**  
`packageQuantity`*(String(1‑999), Y)*, `weight`*(String(0.01‑999.99), Y)*, `packageValue`*(String(3), Y)*, `goodsType`*(String(4), Y: `ITN2` doc, `ITN8` pkg)*, `length/width/height`*(String(0.01‑999.99), N)*

**OfferFeeInfo**  
`offerValue`*(String(0.01‑999999.99), N)* — if present, insurance is enabled (cannot set 0.0).

**CodInfo**  
`codValue`*(String(0.01‑999999.99), N)* — MYR.

**Items (each)**  
`itemName`*(String(100), Y)*, `englishName`*(String(100), N)*, `number`*(String(1‑9999999), Y)*, `weight`*(String(1‑999999), Y)*, `itemValue`*(String(0.01‑9999999.99), Y)*, `itemCurrency`*(String(10), N default MYR)*, `itemDesc`*(String(200), N)*

**CustomsInfo**  
`customsCode`*(String(100), N)*, `nationalInspectionNo`*(String(30), N)*, `number`*(String(1‑999999), Y)*, `weight`*(String(0.01‑999.99), Y)*, `totalValue`*(String(0.01‑9999999), N)*, `unitPrice`*(String(0.01‑9999999), Y)*, `currency`*(String(10), Y)*, `originPlace/brandName`*(N)*, `oldItem`*(String(1), N: 0/1)*

**multipleVotes (each)**  
`actualWeight`*(String(0.01‑999.99), Y)*, `length/width/height`*(String(0.01‑999.99), N)*

**Response `data`**  
`txlogisticId`*(String(50), Y)*, `billCode`*(String(30), Y)*, `sortingCode`*(String(20), Y)*, `thirdSortingCode`*(String(10), Y)*, `multipleVoteBillCodes`*(Array, N)*, `packageChargeWeight`*(String, N)*

**Request Example**
```json
{
  "customerCode": "ITTEST0001",
  "actionType": "add",
  "password": "9C75439FB1FD01EB01861670DD1B949C",
  "txlogisticId": "YLTEST202404101519",
  "expressType": "EZ",
  "serviceType": "1",
  "sender": {
    "name": "J&T sender",
    "postCode": "81930",
    "phone": "60123456",
    "address": "No 32, Jalan Kempas 4",
    "countryCode": "MYS",
    "prov": "Johor",
    "city": "Bandar Penawar",
    "area": "Taman Desaru Utama"
  },
  "receiver": {
    "name": "J&T receiver",
    "postCode": "31000",
    "phone": "60987654",
    "address": "4678, Laluan Sentang 35",
    "countryCode": "MYS",
    "prov": "Perak",
    "city": "Batu Gajah",
    "area": "Kampung Seri Mariah"
  },
  "payType": "PP_PM",
  "goodsType": "PARCEL",
  "weight": 10,
  "items": [
    {
      "itemName": "basketball",
      "englishName": "basketball",
      "itemDesc": "This is a basketball",
      "number": "2",
      "itemValue": "50",
      "weight": "10",
      "itemCurrency": "USD"
    },
    {
      "itemName": "phone",
      "englishName": "phone",
      "itemDesc": "This is a phone",
      "number": "1",
      "itemValue": "4000",
      "weight": "100",
      "itemCurrency": "USD"
    }
  ],
  "packageInfo": {
    "packageQuantity": "10",
    "goodsType": "ITN2",
    "weight": "10",
    "length": "10",
    "width": "10",
    "packageValue": "880"
  },
  "sendStartTime": "2024-06-19 13:45:00",
  "sendEndTime": "2024-06-25 16:23:00",
  "remark": "",
  "returnInfo": {
    "name": "J&T return",
    "postCode": "31000",
    "phone": "60987654",
    "address": "4678, Laluan Sentang 35"
  },
  "offerFeeInfo": {"offerValue": "12"},
  "customsInfo": {
    "customsCode": "2000001",
    "nationalInspectionNo": "456DEF",
    "originPlace": "China",
    "brandName": "Brand X",
    "oldItem": "0",
    "number": "10",
    "weight": "25.5",
    "totalValue": "100",
    "unitPrice": "10",
    "currency": "USD"
  },
  "codInfo": {"codValue": "100"},
  "multipleVotes": [
    {"actualWeight": "21", "length": "12", "width": "12", "height": "12"},
    {"actualWeight": "21", "length": "12", "width": "12", "height": "12"}
  ]
}
```

**Response Example**
```json
{
  "code": 1,
  "msg": "success",
  "data": {
    "txlogisticId": "YLTEST202404101519",
    "billCode": "630002864925",
    "sortingCode": "02-C51-PRK309",
    "thirdSortingCode": "S01",
    "multipleVoteBillCodes": ["630002864925", "630002864925-01"],
    "packageChargeWeight": "12.36"
  },
  "requestId": "9e150ccfeda34150b900b4262cc085d1"
}
```

---

### 4.2 Query Order — `/api/order/getOrders`
**Testing:** `https://demoopenapi.jtexpress.my/webopenplatformapi/api/order/getOrders`  
**Production:** `https://ylopenapi.jtexpress.my/webopenplatformapi/api/order/getOrders`  

**bizContent:** `customerCode`*(Y)*, `password`*(Y)*, `txlogisticId`*(Y)*

**Example Request**
```json
{ "customerCode": "ITTEST0001", "password": "9C75439FB1FD01EB01861670DD1B949C", "txlogisticId": "YLTEST202404101519" }
```

**Example Response (excerpt)**
```json
{
  "code": 1,
  "msg": "success",
  "data": {
    "customerCode": "ITTEST0001",
    "txlogisticId": "YLTEST202404101519",
    "billCode": "630002864925",
    "expressType": "EZ",
    "serviceType": "1",
    "sender": { "name": "J&T sender", "postCode": "81930", "countryCode": "MYS" },
    "receiver": { "name": "J&T receiver", "postCode": "31000", "countryCode": "MYS" },
    "payType": "PP_PM",
    "packageInfo": { "packageQuantity": 10, "goodsType": "ITN2", "packageValue": 880 }
  },
  "requestId": "00027ea9b253402facfe872d1915ec64"
}
```

---

### 4.3 Cancel Order — `/api/order/cancelOrder`
**Testing:** `https://demoopenapi.jtexpress.my/webopenplatformapi/api/order/cancelOrder`  
**Production:** `https://ylopenapi.jtexpress.my/webopenplatformapi/api/order/cancelOrder`  

**bizContent:** `customerCode`*(Y)*, `password`*(Y)*, `txlogisticId`*(Y)*, `billCode`*(N)*, `reason`*(Y)*

**Example Request**
```json
{
  "customerCode": "ITTEST0001",
  "password": "9C75439FB1FD01EB01861670DD1B949C",
  "txlogisticId": "YLTEST202404101520",
  "billCode": "630002563505",
  "reason": "The customer cancelled the order"
}
```

**Example Response**
```json
{ "code": 1, "msg": "success", "data": { "txlogisticId": "YLTEST202404101520", "billCode": "630002563505" }, "requestId": "97512378ad844b3a9668c07a2628da88" }
```

---

### 4.4 Print Order — `/api/order/printOrder`
**Testing:** `https://demoopenapi.jtexpress.my/webopenplatformapi/api/order/printOrder`  
**Production:** `https://ylopenapi.jtexpress.my/webopenplatformapi/api/order/printOrder`  

**bizContent:** `customerCode`*(Y)*, `password`*(Y)*, `txlogisticId`*(Y)*, `billCode`*(N)*, `templateName`*(N)*

**Response `data`:** `txlogisticId`, `billCode`, `base64EncodeContent`*(N)*, `urlContent`*(N)*

**Example Request**
```json
{ "customerCode": "ITTEST0001", "password": "9C75439FB1FD01EB01861670DD1B949C", "txlogisticId": "YLTEST202404101519", "billCode": "630002864925" }
```

**Example Response (excerpt)**
```json
{
  "code": 1,
  "msg": "success",
  "data": {
    "txlogisticId": "KEXMY1000000239996",
    "billCode": "670300032350",
    "base64EncodeContent": "",
    "urlContent": "https://ylopenapi.jtexpress.my/webopenplatformapi/api/pic/file?url=.../print/20250424/8add1a25cd664dce89fc447d38ef6ac7.pdf"
  },
  "requestId": "7a98e6290485419b841b69f974443d54"
}
```

---

### 4.5 Track Parcel — `/api/logistics/trace`
**Testing:** `https://demoopenapi.jtexpress.my/webopenplatformapi/api/logistics/trace`  
**Production:** `https://ylopenapi.jtexpress.my/webopenplatformapi/api/logistics/trace`  

**bizContent:** `customerCode`*(Y)*, `password`*(Y)*, `txlogisticId`*(N)*, `billCode`*(N)* — **one of** `txlogisticId` or `billCode` is required.

**details fields returned:**  
`scanTime`, `desc`, `scanTypeCode`, `scanTypeName`, `scanType`, `realWeight`, `scanNetworkTypeName`, `scanNetworkName`, `staffName`, `staffContact`(N), `scanNetworkContact`(N), `scanNetworkProvince/City/Area`, `sigPicUrl`(N), `longitude`(N), `latitude`(N), `timeZone`(N).

**Example Request**
```json
{ "customerCode": "ITTEST0001", "password": "9C75439FB1FD01EB01861670DD1B949C", "billCode": "630002864925" }
```

---

### 4.6 Tracking Callback (Webhook Push)
**Callback URL:** Provided by merchant (e.g., `https://yourdomain.com/jt/callback`)  
**Method/Type:** `POST` `application/x-www-form-urlencoded`

**Headers:** `apiAccount`, `digest`, `timestamp`

**Payload (`bizContent`)** is an **array** of push items:  
Each item contains `billCode`, optional `txlogisticId`, and `details[]` (same fields as 4.5).

**Expected Response**
```json
{ "code": "1", "msg": "success", "data": "SUCCESS", "requestId": "211212121212" }
```

**Security:** Verify `digest` using your `privateKey`. Use HTTPS. J&T may batch multiple updates per push.

---

## 5) Status Codes (Unified)
**Success:** `1` · **Fail:** `0`  
Common platform codes:  
- `145003052` digest is empty  
- `145003051` apiAccount is empty  
- `145003053` timestamp is empty  
- `145003010` API account does not exist  
- `145003012` API account has no interface permissions  
- `145003030` headers signature verification failed  
- `145003050` Illegal parameters  

**Order validation examples (non‑exhaustive):**  
Missing/invalid fields for sender/receiver/items/packageInfo/customsInfo/etc., including value ranges (weight/length/price), requiredness per country, and format checks (time/date).

---

## 6) Signature Examples

### 6.1 JavaScript (Node.js)
```js
const crypto = require('crypto');

function makeDigest(bizContentJsonString, privateKey) {
  const toSign = bizContentJsonString + privateKey;
  const md5 = crypto.createHash('md5').update(toSign, 'utf8').digest(); // raw bytes
  return Buffer.from(md5).toString('base64');
}

// usage
const biz = { customerCode: "ITTEST0001", txlogisticId: "YLTEST..." };
const jsonStr = JSON.stringify(biz, null, 0); // keep stable; avoid reformatting after signing
const digest = makeDigest(jsonStr, "8e88c8477d4e4939859c560192fcafbc");
console.log(digest);
```

### 6.2 PHP (Laravel compatible)
```php
<?php
function makeDigest(string $bizContentJsonString, string $privateKey): string {
    $toSign = $bizContentJsonString . '8e88c8477d4e4939859c560192fcafbc';
    $md5Raw = md5($toSign, true); // raw bytes
    return base64_encode($md5Raw);
}
$b = ['customerCode' => 'ITTEST0001', 'txlogisticId' => 'YLTEST...'];
$json = json_encode($b, JSON_UNESCAPED_UNICODE);
$digest = makeDigest($json, '8e88c8477d4e4939859c560192fcafbc');
echo $digest;
```

---

## 7) Postman Collection (Sandbox Defaults)
Import the JSON below into Postman/Insomnia. It uses the **Testing** base URL and pre-fills `apiAccount`, `privateKey`, and example bodies. Update `customerCode/password` to your own test values when provided by J&T.

```json
{
  "info": {
    "name": "J&T MY Open API (Sandbox)",
    "_postman_id": "e7f0e6b5-1a1f-4c20-9c44-6b3d3c9a0001",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Create Order",
      "request": {
        "method": "POST",
        "header": [
          { "key": "apiAccount", "value": "640826271705595946" },
          { "key": "digest", "value": "<compute at runtime>" },
          { "key": "timestamp", "value": "{{timestamp}}" }
        ],
        "url": "https://demoopenapi.jtexpress.my/webopenplatformapi/api/order/addOrder",
        "body": {
          "mode": "urlencoded",
          "urlencoded": [ {"key":"bizContent","value":"<PUT_JSON_HERE>","type":"text"} ]
        }
      }
    },
    {
      "name": "Query Order",
      "request": {
        "method": "POST",
        "header": [
          { "key": "apiAccount", "value": "640826271705595946" },
          { "key": "digest", "value": "<compute at runtime>" },
          { "key": "timestamp", "value": "{{timestamp}}" }
        ],
        "url": "https://demoopenapi.jtexpress.my/webopenplatformapi/api/order/getOrders",
        "body": {
          "mode": "urlencoded",
          "urlencoded": [ {"key":"bizContent","value":"{{getOrders_biz}}","type":"text"} ]
        }
      }
    },
    {
      "name": "Cancel Order",
      "request": {
        "method": "POST",
        "header": [
          { "key": "apiAccount", "value": "640826271705595946" },
          { "key": "digest", "value": "<compute at runtime>" },
          { "key": "timestamp", "value": "{{timestamp}}" }
        ],
        "url": "https://demoopenapi.jtexpress.my/webopenplatformapi/api/order/cancelOrder",
        "body": {
          "mode": "urlencoded",
          "urlencoded": [ {"key":"bizContent","value":"{{cancelOrder_biz}}","type":"text"} ]
        }
      }
    },
    {
      "name": "Print Order",
      "request": {
        "method": "POST",
        "header": [
          { "key": "apiAccount", "value": "640826271705595946" },
          { "key": "digest", "value": "<compute at runtime>" },
          { "key": "timestamp", "value": "{{timestamp}}" }
        ],
        "url": "https://demoopenapi.jtexpress.my/webopenplatformapi/api/order/printOrder",
        "body": {
          "mode": "urlencoded",
          "urlencoded": [ {"key":"bizContent","value":"{{printOrder_biz}}","type":"text"} ]
        }
      }
    },
    {
      "name": "Track Parcel",
      "request": {
        "method": "POST",
        "header": [
          { "key": "apiAccount", "value": "640826271705595946" },
          { "key": "digest", "value": "<compute at runtime>" },
          { "key": "timestamp", "value": "{{timestamp}}" }
        ],
        "url": "https://demoopenapi.jtexpress.my/webopenplatformapi/api/logistics/trace",
        "body": {
          "mode": "urlencoded",
          "urlencoded": [ {"key":"bizContent","value":"{{trace_biz}}","type":"text"} ]
        }
      }
    },
    {
      "name": "Webhook (Example Receiver)",
      "request": {
        "method": "POST",
        "header": [
          { "key": "apiAccount", "value": "640826271705595946" },
          { "key": "digest", "value": "<compute at runtime>" },
          { "key": "timestamp", "value": "{{timestamp}}" }
        ],
        "url": "https://yourdomain.com/jt/callback",
        "body": {
          "mode": "raw",
          "raw": "{{webhook_payload}}"
        }
      }
    }
  ],
  "variable": [
    {"key":"timestamp","value":"1759908138699"},
    {"key":"getOrders_biz","value":"{{\"customerCode\":\"ITTEST0001\",\"password\":\"9C75439FB1FD01EB01861670DD1B949C\",\"txlogisticId\":\"YLTEST202404101519\"}}"},
    {"key":"cancelOrder_biz","value":"{{\"customerCode\":\"ITTEST0001\",\"password\":\"9C75439FB1FD01EB01861670DD1B949C\",\"txlogisticId\":\"YLTEST202404101520\",\"billCode\":\"630002563505\",\"reason\":\"The customer cancelled the order\"}}"},
    {"key":"printOrder_biz","value":"{{\"customerCode\":\"ITTEST0001\",\"password\":\"9C75439FB1FD01EB01861670DD1B949C\",\"txlogisticId\":\"YLTEST202404101519\",\"billCode\":\"630002864925\"}}"},
    {"key":"trace_biz","value":"{{\"customerCode\":\"ITTEST0001\",\"password\":\"9C75439FB1FD01EB01861670DD1B949C\",\"billCode\":\"630002864925\"}}"},
    {"key":"webhook_payload","value":"{{\"bizContent\":[{\"billCode\":\"JMX100099499533\",\"txlogisticId\":\"JMX100099499533\",\"details\":[{\"scanTime\":\"2024-03-11 14:00:45\",\"desc\":\"Parcel out for delivery.\",\"scanType\":\"On Delivery\",\"scanTypeCode\":\"94\",\"scanNetworkName\":\"JLP-Sierra.pdv\",\"scanNetworkProvince\":\"Querétaro\",\"scanNetworkCity\":\"Jalpan de Serra\",\"scanNetworkArea\":\"Centro\",\"timeZone\":\"GMT+08:00\"}]}]}"}
  ]
}
```

---

## 8) Change Log
- 2025-10-08: First consolidated English‑only reference with full endpoint specs, signature, status codes, examples, and Postman collection.

