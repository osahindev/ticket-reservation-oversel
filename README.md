# Konser Bileti Hücumu

## Çözüm Genel Bakışı
Bu proje, konser biletlerinin eşzamanlı olarak rezerve edilmesini sağlayan bir sistemdir. Laravel framework'ü kullanılarak geliştirilmiştir ve Docker ile konteynerize edilmiştir. PostgreSQL veritabanı, bilet rezervasyonları ve eşzamanlılık problemlerini çözmek için kullanılmıştır.

## Eşzamanlılık Stratejisi

Yarış durumlarını önlemek için kötümser kilitleme (`lockForUpdate`) kullanılmıştır. Bu yaklaşım, aynı anda birden fazla işlemin aynı kaydı değiştirmesini engeller. 

- **Avantajlar**: Veri tutarlılığı garanti edilir.
- **Dezavantajlar**: Kilitlenme durumlarında performans düşebilir.

## Veritabanı Şeması

### Modeller ve İlişkiler

- **Event**: Bir etkinliği temsil eder.
- **Reservation**: Bir etkinlik için yapılan bilet rezervasyonunu temsil eder.
- **İlişkiler**: `Event` birden fazla `Reservation` ile ilişkilidir.

## API Endpoint'leri

1. Reservasyon Oluşturma

* Örnek istek:

~~~bash
curl -X POST http://localhost/api/reserve \
  -H "Content-Type: application/json" \
  -d '{"event_id": 1, "amount": 1}'
~~~

* Örnek yanıt:

~~~json
{
    "data": {
        "id": 4,
        "user_uid": "c9439d95-2123-4d0b-8967-50903fc0b9a8",
        "amount": 10,
        "status": "reserved",
        "expires_at": "2025-11-18T07:54:23.000000Z",
        "created_at": "2025-11-18T07:49:23.000000Z",
        "updated_at": "2025-11-18T07:49:23.000000Z"
    }
}
~~~

2. Satın Alma

* Örnek istek:

~~~bash
curl -X POST http://localhost/api/purchase \
  -H "Content-Type: application/json" \
  -H "Visitor-Token: c9439d95-2123-4d0b-8967-50903fc0b9a8"
  -d '{"reservation_id": 1}'
~~~

* Örnek yanıt:

~~~json
{
    "data": {
        "id": 6,
        "user_uid": "397fbc8d-4313-49ee-96fe-b61274b7331a",
        "amount": 10,
        "status": "purchased",
        "expires_at": "2025-11-18T08:11:02.000000Z",
        "created_at": "2025-11-18T08:06:02.000000Z",
        "updated_at": "2025-11-18T08:06:09.000000Z"
    }
}
~~~