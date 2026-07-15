# 🔌 Documentación de API - Faristol

## 📋 Información General

### URL Base
```
https://api.faristol.net/api
```

### Versión Actual
- **Versión**: v1.0
- **Formato**: JSON
- **Codificación**: UTF-8
- **Rate Limiting**: Activo

### Autenticación
La API utiliza Laravel Sanctum para autenticación. Incluye el token en el header:

```http
Authorization: Bearer {tu_token}
Content-Type: application/json
Accept: application/json
```

### Rate Limiting
- **Endpoints públicos**: 60 requests/minuto por IP
- **Endpoints autenticados**: 1000 requests/minuto por usuario
- **Headers de respuesta**:
  - `X-RateLimit-Limit`: Límite total
  - `X-RateLimit-Remaining`: Requests restantes
  - `X-RateLimit-Reset`: Timestamp de reset

## 🔐 Autenticación

### Registro de Usuario
```http
POST /api/auth/user/signup
```

**Request Body:**
```json
{
    "name": "Juan Pérez",
    "email": "juan@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "telephone": "123456789",
    "country_code": "34"
}
```

**Response (201):**
```json
{
    "status": true,
    "message": "Usuario registrado exitosamente",
    "data": {
        "user": {
            "id": 1,
            "name": "Juan Pérez",
            "email": "juan@example.com",
            "telephone": "(+34)123456789",
            "email_verified_at": null,
            "created_at": "2024-01-15T10:00:00.000000Z"
        },
        "token": "1|abc123def456ghi789..."
    }
}
```

### Login
```http
POST /api/auth/login
```

**Request Body:**
```json
{
    "email": "juan@example.com",
    "password": "password123"
}
```

**Response (200):**
```json
{
    "status": true,
    "message": "Login exitoso",
    "data": {
        "user": {
            "id": 1,
            "name": "Juan Pérez",
            "email": "juan@example.com",
            "roles": ["musician"],
            "subscription": {
                "level": 0,
                "plan_name": "Free"
            }
        },
        "token": "2|def456ghi789jkl012...",
        "expires_at": "2024-02-15T10:00:00.000000Z"
    }
}
```

### Logout
```http
POST /api/auth/logout
Authorization: Bearer {token}
```

### Recuperación de Contraseña

#### Solicitar OTP
```http
POST /api/auth/user/request-otp
```

**Request Body:**
```json
{
    "email": "juan@example.com"
}
```

#### Verificar OTP
```http
POST /api/auth/user/verify-otp
```

**Request Body:**
```json
{
    "email": "juan@example.com",
    "otp": "123456"
}
```

#### Cambiar Contraseña
```http
POST /api/auth/user/change-password
```

**Request Body:**
```json
{
    "email": "juan@example.com",
    "otp": "123456",
    "password": "nueva_password123",
    "password_confirmation": "nueva_password123"
}
```

## 👤 Gestión de Usuario

### Obtener Perfil
```http
GET /api/auth/user/check-user
Authorization: Bearer {token}
```

**Response (200):**
```json
{
    "status": true,
    "data": {
        "id": 1,
        "name": "Juan Pérez",
        "email": "juan@example.com",
        "telephone": "(+34)123456789",
        "email_verified_at": "2024-01-15T10:30:00.000000Z",
        "roles": ["musician"],
        "subscription": {
            "level": 1,
            "plan_name": "Basic",
            "is_paid": true,
            "expires_at": "2024-02-15T10:00:00.000000Z"
        },
        "premium_trial": {
            "used_count": 0,
            "available": true
        }
    }
}
```

### Actualizar Perfil
```http
POST /api/auth/user/edit/{id}
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body:**
```json
{
    "name": "Juan Carlos Pérez",
    "telephone": "987654321",
    "country_code": "34",
    "profile_image": "base64_image_data"
}
```

### Verificar Estado de Suscripción
```http
GET /api/auth/user/check-subscription
Authorization: Bearer {token}
```

### Verificar Estado de Compositor
```http
GET /api/auth/user/check-composer
Authorization: Bearer {token}
```

## 🎼 Compositores

### Listar Compositores
```http
GET /api/composer/list
```

**Query Parameters:**
- `page`: Número de página (default: 1)
- `limit`: Items por página (default: 20, max: 100)
- `search`: Búsqueda por nombre
- `nationality`: Filtrar por nacionalidad
- `period`: Filtrar por período (classical, romantic, modern, etc.)

**Response (200):**
```json
{
    "status": true,
    "data": {
        "composers": [
            {
                "id": 1,
                "name": "Johann Sebastian Bach",
                "nationality": "German",
                "birth_date": "1685-03-31",
                "death_date": "1750-07-28",
                "period": "Baroque",
                "biography": "Compositor alemán del período barroco...",
                "image_url": "https://storage.faristol.net/composers/bach.jpg",
                "music_scores_count": 156,
                "status": "approved"
            }
        ],
        "pagination": {
            "current_page": 1,
            "total_pages": 15,
            "total_items": 298,
            "per_page": 20
        }
    }
}
```

### Obtener Compositor
```http
GET /api/composer/{id}
```

**Response (200):**
```json
{
    "status": true,
    "data": {
        "id": 1,
        "name": "Johann Sebastian Bach",
        "nationality": "German",
        "birth_date": "1685-03-31",
        "death_date": "1750-07-28",
        "period": "Baroque",
        "biography": "Johann Sebastian Bach fue un compositor...",
        "image_url": "https://storage.faristol.net/composers/bach.jpg",
        "music_scores": [
            {
                "id": 1,
                "name": "Invención No. 1 en Do Mayor",
                "difficulty": "intermediate",
                "instruments": ["Piano"],
                "style": "Baroque"
            }
        ],
        "statistics": {
            "total_scores": 156,
            "total_downloads": 25847,
            "average_rating": 4.8
        }
    }
}
```

### Crear Compositor (Autenticado)
```http
POST /api/composer/create
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body:**
```json
{
    "name": "Nuevo Compositor",
    "nationality": "Spanish",
    "birth_date": "1990-01-01",
    "death_date": null,
    "period": "Contemporary",
    "biography": "Biografía del compositor...",
    "image": "base64_image_data"
}
```

## 🎹 Instrumentos

### Listar Instrumentos
```http
GET /api/instruments/list
```

**Query Parameters:**
- `family_id`: Filtrar por familia de instrumento
- `search`: Búsqueda por nombre

**Response (200):**
```json
{
    "status": true,
    "data": {
        "instruments": [
            {
                "id": 1,
                "name": "Piano",
                "family": {
                    "id": 1,
                    "name": "Teclado"
                },
                "description": "Instrumento de teclas...",
                "music_scores_count": 1250
            },
            {
                "id": 2,
                "name": "Violín",
                "family": {
                    "id": 2,
                    "name": "Cuerda"
                },
                "description": "Instrumento de cuerda frotada...",
                "music_scores_count": 890
            }
        ]
    }
}
```

### Familias de Instrumentos
```http
GET /api/instruments/family/list
```

### Sugerir Instrumento (Autenticado)
```http
POST /api/instruments/suggest
Authorization: Bearer {token}
```

**Request Body:**
```json
{
    "name": "Nuevo Instrumento",
    "family_id": 1,
    "description": "Descripción del instrumento"
}
```

## 🎨 Estilos Musicales

### Listar Estilos
```http
GET /api/style-music/list
```

**Response (200):**
```json
{
    "status": true,
    "data": {
        "styles": [
            {
                "id": 1,
                "name": "Classical",
                "description": "Música clásica europea...",
                "music_scores_count": 2150
            },
            {
                "id": 2,
                "name": "Jazz",
                "description": "Género musical nacido en Estados Unidos...",
                "music_scores_count": 458
            }
        ]
    }
}
```

## 🎼 Partituras Musicales

### Listar Partituras
```http
GET /api/music-score/list
```

**Query Parameters:**
- `page`: Página (default: 1)
- `limit`: Items por página (max: 100)
- `search`: Búsqueda por título
- `composer_id`: Filtrar por compositor
- `instrument_id`: Filtrar por instrumento
- `style_id`: Filtrar por estilo
- `difficulty`: Filtrar por dificultad (beginner, intermediate, advanced)
- `sort`: Ordenar por (name, created_at, downloads, rating)
- `order`: Dirección (asc, desc)

**Response (200):**
```json
{
    "status": true,
    "data": {
        "music_scores": [
            {
                "id": 1,
                "name": "Invención No. 1 en Do Mayor",
                "composer": {
                    "id": 1,
                    "name": "Johann Sebastian Bach"
                },
                "instruments": [
                    {
                        "id": 1,
                        "name": "Piano"
                    }
                ],
                "style": {
                    "id": 1,
                    "name": "Baroque"
                },
                "difficulty": "intermediate",
                "duration_minutes": 2,
                "description": "Primera invención a dos voces...",
                "thumbnail_url": "https://storage.faristol.net/thumbnails/1.jpg",
                "pdf_pages": 2,
                "created_at": "2024-01-10T10:00:00Z",
                "statistics": {
                    "downloads": 1250,
                    "views": 5430,
                    "favorites": 89,
                    "rating": 4.7
                },
                "user_stats": {
                    "is_favorite": false,
                    "download_count": 0,
                    "last_viewed": null
                }
            }
        ],
        "pagination": {
            "current_page": 1,
            "total_pages": 85,
            "total_items": 1687,
            "per_page": 20
        },
        "filters": {
            "applied": {
                "composer_id": 1,
                "difficulty": "intermediate"
            },
            "available": {
                "composers": 45,
                "instruments": 25,
                "styles": 12,
                "difficulties": ["beginner", "intermediate", "advanced"]
            }
        }
    }
}
```

### Búsqueda Avanzada
```http
GET /api/music-score/list-filtered
```

**Query Parameters adicionales:**
- `tags[]`: Array de tags
- `date_from`: Fecha desde (YYYY-MM-DD)
- `date_to`: Fecha hasta (YYYY-MM-DD)
- `has_audio`: Con audio (true/false)
- `free_only`: Solo gratuitas (true/false)

### Obtener Partitura
```http
GET /api/music-score/get/{id}
Authorization: Bearer {token}
```

**Response (200):**
```json
{
    "status": true,
    "data": {
        "id": 1,
        "name": "Invención No. 1 en Do Mayor",
        "composer": {
            "id": 1,
            "name": "Johann Sebastian Bach",
            "period": "Baroque"
        },
        "instruments": [
            {
                "id": 1,
                "name": "Piano",
                "family": "Keyboard"
            }
        ],
        "style": {
            "id": 1,
            "name": "Baroque"
        },
        "difficulty": "intermediate",
        "duration_minutes": 2,
        "key_signature": "C Major",
        "time_signature": "4/4",
        "tempo": "Allegro moderato",
        "description": "Primera invención a dos voces en Do Mayor...",
        "files": [
            {
                "id": 1,
                "type": "pdf",
                "url": "https://storage.faristol.net/scores/1/main.pdf",
                "pages": 2,
                "size_bytes": 245760
            },
            {
                "id": 2,
                "type": "audio",
                "url": "https://storage.faristol.net/scores/1/audio.mp3",
                "duration_seconds": 120,
                "size_bytes": 2457600
            }
        ],
        "tags": ["educational", "two-part", "baroque"],
        "created_at": "2024-01-10T10:00:00Z",
        "updated_at": "2024-01-12T15:30:00Z"
    }
}
```

### Crear Partitura (Autenticado)
```http
POST /api/music-score/create
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body:**
```json
{
    "name": "Mi Nueva Partitura",
    "composer_id": 1,
    "instruments[]": [1, 2],
    "style_music_id": 1,
    "difficulty": "intermediate",
    "duration_minutes": 5,
    "key_signature": "G Major",
    "time_signature": "3/4",
    "tempo": "Andante",
    "description": "Descripción de la partitura...",
    "tags[]": ["original", "waltz"],
    "files[]": ["file1.pdf", "file2.pdf"],
    "audio": "audio.mp3",
    "thumbnail": "image.jpg"
}
```

### Obtener PDF
```http
GET /api/music-score/getMusicScorePdf/{id}
Authorization: Bearer {token}
```

**Query Parameters:**
- `page`: Página específica del PDF
- `quality`: Calidad (low, medium, high)

### Obtener Contenido de PDF por Páginas
```http
POST /api/music-score/getPdfContent
Authorization: Bearer {token}
```

**Request Body:**
```json
{
    "music_score_id": 1,
    "page": 1,
    "quality": "medium"
}
```

**Response (200):**
```json
{
    "status": true,
    "data": {
        "page": 1,
        "total_pages": 4,
        "image_url": "https://storage.faristol.net/scores/1/pages/page-1-medium.jpg",
        "annotations_allowed": true,
        "watermark": false
    }
}
```

### Favoritos

#### Agregar a Favoritos
```http
GET /api/music-score/fav-music-score?music_score_id={id}
Authorization: Bearer {token}
```

#### Remover de Favoritos
```http
GET /api/music-score/remove-fav-music-score?music_score_id={id}
Authorization: Bearer {token}
```

#### Listar Favoritos
```http
GET /api/music-score/user-fav-music-score
Authorization: Bearer {token}
```

## 💳 Suscripciones

### Obtener Planes
```http
GET /api/subscription/subscription-plans
```

**Response (200):**
```json
{
    "status": true,
    "data": {
        "plans": [
            {
                "id": 1,
                "name": "Free",
                "type": 0,
                "price": 0,
                "currency": "EUR",
                "duration_days": null,
                "features": {
                    "annotations": 5,
                    "advertisements": true,
                    "favorites": false,
                    "priority_support": false
                },
                "description": "Plan gratuito con funcionalidades básicas"
            },
            {
                "id": 2,
                "name": "Basic",
                "type": 1,
                "price": 9.99,
                "currency": "EUR",
                "duration_days": 30,
                "features": {
                    "annotations": 15,
                    "advertisements": false,
                    "favorites": false,
                    "priority_support": false
                },
                "description": "Plan básico sin anuncios"
            },
            {
                "id": 3,
                "name": "Premium",
                "type": 2,
                "price": 19.99,
                "currency": "EUR",
                "duration_days": 30,
                "features": {
                    "annotations": "unlimited",
                    "advertisements": false,
                    "favorites": true,
                    "priority_support": true
                },
                "description": "Plan premium con todas las funcionalidades"
            }
        ]
    }
}
```

### Suscribirse a Plan
```http
POST /api/inapp-subscription/sync-subscribe
Authorization: Bearer {token}
```

**Request Body:**
```json
{
    "type": 2,
    "payment_method": "paypal",
    "return_url": "https://app.faristol.net/payment/success",
    "cancel_url": "https://app.faristol.net/payment/cancel"
}
```

### Aplicar Prueba Premium
```http
POST /api/inapp-subscription/apply-premium-trial
Authorization: Bearer {token}
```

### Estado de Suscripción
```http
GET /api/subscription/subscription-status
Authorization: Bearer {token}
```

## 🎵 Solicitudes de Compositor

### Listar Solicitudes (Admin)
```http
GET /api/composer-request/list
Authorization: Bearer {token}
```

### Crear Solicitud
```http
POST /api/composer-request/create
Authorization: Bearer {token}
```

**Request Body:**
```json
{
    "name": "Mi Nombre Artístico",
    "real_name": "Mi Nombre Real",
    "nationality": "Spanish",
    "birth_date": "1990-01-01",
    "biography": "Mi biografía como compositor...",
    "portfolio_url": "https://mi-portfolio.com",
    "social_media": {
        "website": "https://mi-web.com",
        "youtube": "https://youtube.com/mi-canal",
        "instagram": "@mi_instagram"
    },
    "musical_education": "Conservatorio Superior de Música",
    "genres": ["Classical", "Contemporary"],
    "sample_works": [
        {
            "title": "Sonata Op. 1",
            "year": 2023,
            "url": "https://soundcloud.com/mi-obra"
        }
    ]
}
```

## 📊 Estadísticas y Analytics

### Estadísticas de Partitura
```http
GET /api/music-score/statistics/{id}
Authorization: Bearer {token}
```

**Response (200):**
```json
{
    "status": true,
    "data": {
        "music_score_id": 1,
        "views": {
            "total": 5430,
            "this_month": 456,
            "this_week": 89
        },
        "downloads": {
            "total": 1250,
            "this_month": 123,
            "this_week": 23
        },
        "favorites": {
            "total": 89,
            "this_month": 12
        },
        "ratings": {
            "average": 4.7,
            "total_votes": 234,
            "distribution": {
                "5": 145,
                "4": 67,
                "3": 18,
                "2": 3,
                "1": 1
            }
        },
        "demographics": {
            "countries": {
                "ES": 45,
                "FR": 23,
                "DE": 18,
                "IT": 14
            },
            "devices": {
                "mobile": 67,
                "desktop": 28,
                "tablet": 5
            }
        }
    }
}
```

## 🔔 Webhooks

### PayPal Webhook
```http
POST /api/subscription/paypal-webhook
```

Este endpoint recibe notificaciones de PayPal sobre cambios en suscripciones.

## 🚨 Códigos de Error

### Códigos HTTP
- `200`: OK - Solicitud exitosa
- `201`: Created - Recurso creado exitosamente
- `400`: Bad Request - Datos de solicitud inválidos
- `401`: Unauthorized - Token de autenticación requerido/inválido
- `403`: Forbidden - Permisos insuficientes
- `404`: Not Found - Recurso no encontrado
- `422`: Unprocessable Entity - Errores de validación
- `429`: Too Many Requests - Rate limit excedido
- `500`: Internal Server Error - Error del servidor

### Estructura de Error
```json
{
    "status": false,
    "message": "Mensaje de error principal",
    "errors": {
        "field_name": [
            "Descripción específica del error"
        ]
    },
    "error_code": "VALIDATION_FAILED",
    "timestamp": "2024-01-15T10:00:00Z"
}
```

### Códigos de Error Específicos
- `AUTH_FAILED`: Error de autenticación
- `VALIDATION_FAILED`: Error de validación
- `SUBSCRIPTION_REQUIRED`: Suscripción requerida
- `TRIAL_EXHAUSTED`: Prueba premium agotada
- `FILE_TOO_LARGE`: Archivo demasiado grande
- `UNSUPPORTED_FORMAT`: Formato no soportado

## 📚 Ejemplos de Uso

### Cliente JavaScript
```javascript
// Configuración base
const API_BASE = 'https://api.faristol.net/api';
const token = localStorage.getItem('auth_token');

// Headers por defecto
const defaultHeaders = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Authorization': `Bearer ${token}`
};

// Función helper para requests
async function apiRequest(endpoint, options = {}) {
    const response = await fetch(`${API_BASE}${endpoint}`, {
        headers: defaultHeaders,
        ...options
    });
    
    if (!response.ok) {
        throw new Error(`API Error: ${response.status}`);
    }
    
    return response.json();
}

// Ejemplo: Buscar partituras
async function searchMusicScores(query, filters = {}) {
    const params = new URLSearchParams({
        search: query,
        ...filters
    });
    
    return apiRequest(`/music-score/list?${params}`);
}

// Ejemplo: Subir partitura
async function uploadMusicScore(formData) {
    return fetch(`${API_BASE}/music-score/create`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        },
        body: formData
    }).then(r => r.json());
}
```

### Cliente PHP
```php
<?php
class FaristolAPI {
    private $baseUrl;
    private $token;
    
    public function __construct($baseUrl, $token = null) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->token = $token;
    }
    
    private function request($method, $endpoint, $data = null) {
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        if ($this->token) {
            $headers[] = "Authorization: Bearer {$this->token}";
        }
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->baseUrl . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $data ? json_encode($data) : null
        ]);
        
        $response = curl_exec($curl);
        curl_close($curl);
        
        return json_decode($response, true);
    }
    
    public function login($email, $password) {
        return $this->request('POST', '/auth/login', [
            'email' => $email,
            'password' => $password
        ]);
    }
    
    public function getMusicScores($filters = []) {
        $query = http_build_query($filters);
        return $this->request('GET', "/music-score/list?{$query}");
    }
}

// Uso
$api = new FaristolAPI('https://api.faristol.net/api');
$login = $api->login('user@example.com', 'password');

if ($login['status']) {
    $api = new FaristolAPI('https://api.faristol.net/api', $login['data']['token']);
    $scores = $api->getMusicScores(['composer_id' => 1]);
}
?>
```

## 🧪 Testing de API

### Colección Postman
Disponible en: [Postman Collection](../postman/faristol-api.json)

### Ejemplos con cURL
```bash
# Login
curl -X POST https://api.faristol.net/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password123"}'

# Buscar partituras
curl -X GET "https://api.faristol.net/api/music-score/list?search=bach&limit=5" \
  -H "Accept: application/json"

# Obtener perfil (autenticado)
curl -X GET https://api.faristol.net/api/auth/user/check-user \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

---

**Documentación actualizada**: 15 de Enero, 2024  
**Versión API**: v1.0  
**Soporte**: api-support@faristol.net
