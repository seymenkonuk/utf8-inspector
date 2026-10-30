# UTF-8 Inspector
> UTF-8 kodlamasının byte yapısını inceleyen ve karakter dizilerini analiz eden PHP uygulaması.

## UTF-8 Nedir?
UTF-8, Unicode karakterlerini 1 ile 4 byte arasında değişken uzunluklarda kodlayan bir karakter kodlama biçimidir.

UTF-8'de her karakterin kaç byte uzunluğunda olduğunu belirlemek için byte'ların bit desenlerinden yararlanılır. İlk byte'ın başlangıç bitleri, karakterin toplam uzunluğunu belirtir:

| **Byte Sayısı** | **İlk Byte** |
|-----------------|--------------|
| 1               | 0XXXXXXX     |
| 2               | 110XXXXX     |
| 3               | 1110XXXX     |
| 4               | 11110XXX     |

Buna göre UTF-8 karakterlerinin byte yapıları şu şekildedir:

```
1 byte: 0XXXXXXX
2 byte: 110XXXXX 10XXXXXX
3 byte: 1110XXXX 10XXXXXX 10XXXXXX
4 byte: 11110XXX 10XXXXXX 10XXXXXX 10XXXXXX
```

UTF-8, ASCII ile **geriye dönük uyumludur**. ASCII standardında tanımlanan 128 karakterin tamamı UTF-8 içerisinde aynı byte değerleriyle temsil edilir. Bu sayede ASCII ile kodlanmış mevcut metinler UTF-8 olarak çözümlendiğinde herhangi bir değişikliğe uğramaz. Aynı şekilde UTF-8 ile kodlanmış metinler başka ASCII uyumlu kodlamalarla çözümlendiğinde de **ASCII karakterleri** doğru görüntülenmeye devam eder.

UTF-8'de Türkçe karakterlerin çoğu 2 byte, emoji gibi karakterler ise 4 byte uzunluğundadır.

## Problem
PHP'de bir metin üzerinde indis kullanılarak yapılan erişimlerde, indisler karakterleri değil byte'ları temsil eder:

```php
$metin = "Çay";
echo strlen($metin);    // 4 (byte uzunluğu)
echo mb_strlen($metin); // 3 (gerçek karakter sayısı)

echo ($metin[0] === "Ç");   // false ("Ç" değil "Ç"nin ilk byte'ı)
echo ($metin[1] === "Ç");   // false ("Ç" değil "Ç"nin ikinci byte'ı)
echo $metin[2];             // a
```

Dolayısıyla metni aşağıdaki gibi bir kodla tersten yazdırmak istediğimizde sonuç hatalı olacaktır:

```php
$ters = "";

for ($i = strlen($metin) - 1; $i >= 0; $i--) {
    $ters .= $metin[$i]
}

echo $ters;
```

Çünkü bu işlem sırasında çok byte'lı karakterlerin byte sıraları da tersine çevrilir. Oysa bir karakteri oluşturan byte'ların kendi içindeki sırası korunmalıdır:

```
Ç                   A        Y
[11000111 10000111] 01100001 01111001

Y         A       (Ç değil)
01111001 01100001 [10000111 11000111]
```

Bunu çözmek için öncelikle metindeki karakterleri, kendilerini oluşturan byte'ları bir arada tutacak şekilde iki boyutlu bir diziye ayırmalıyız. Bu dizide $karakterler[$i], ilgili karakterin byte'larını içeren bir diziyi temsil eder.

Daha sonra dış diziyi tersten dolaşırken her karakterin kendi byte'larını özgün sırasıyla birleştirmeliyiz:

```php
$ters = "";
$karakterler = utf8_characters($metin);

for ($i = count($karakterler) - 1; $i >= 0; $i--) {
    $karakter = $karakterler[$i];
    for ($j = 0; $j < count($karakter); $j++) {
        $ters .= $karakter[$j];
    }
}

echo $ters;
```

## Önerilen Kaynaklar
[RFC 3629: UTF-8](https://www.rfc-editor.org/info/rfc3629)
