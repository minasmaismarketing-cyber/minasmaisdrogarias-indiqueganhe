# AppsFlyer Integration - Technical Documentation

## Overview

This document describes the architecture and implementation of the AppsFlyer integration preparation for the Indique e Ganhe system.

## Architecture

### Principles

The AppsFlyer integration follows SOLID principles:

- **Single Responsibility**: Each class has a single responsibility
- **Open/Closed**: Open for extension, closed for modification
- **Liskov Substitution**: Interfaces and implementations are interchangeable
- **Interface Segregation**: Interfaces are focused and specific
- **Dependency Inversion**: High-level modules don't depend on low-level modules

### Isolation

All AppsFlyer communication is isolated in the Services layer. Controllers do not communicate directly with AppsFlyer.

## Database Schema

### Table: appsflyer_events

| Column | Type | Description |
|--------|------|-------------|
| id | INT UNSIGNED | Primary key |
| usuario_id | INT UNSIGNED | User ID (nullable) |
| indicacao_id | INT UNSIGNED | Indication ID (nullable) |
| appsflyer_id | VARCHAR(255) | AppsFlyer device ID |
| event_name | VARCHAR(100) | Event name |
| event_value | DECIMAL(10, 2) | Event value |
| install_type | ENUM | Install type (FIRST_INSTALL, REINSTALL, REENGAGEMENT, UNKNOWN) |
| media_source | VARCHAR(100) | Media source |
| campaign | VARCHAR(255) | Campaign name |
| campaign_id | VARCHAR(100) | Campaign ID |
| af_status | ENUM | Status (PENDING, RECEIVED, VALIDATED, INVALID) |
| platform | VARCHAR(20) | Platform (android, ios, web) |
| raw_payload | TEXT | Original JSON payload |
| created_at | TIMESTAMP | Creation timestamp |

### Indexes

- idx_appsflyer_usuario (usuario_id)
- idx_appsflyer_indicacao (indicacao_id)
- idx_appsflyer_appsflyer_id (appsflyer_id)
- idx_appsflyer_status (af_status)
- idx_appsflyer_created (created_at)

## Enums

### InstallType

```php
enum InstallType: string
{
    case FIRST_INSTALL = 'FIRST_INSTALL';
    case REINSTALL = 'REINSTALL';
    case REENGAGEMENT = 'REENGAGEMENT';
    case UNKNOWN = 'UNKNOWN';
}
```

### AppsFlyerStatus

```php
enum AppsFlyerStatus: string
{
    case PENDING = 'PENDING';
    case RECEIVED = 'RECEIVED';
    case VALIDATED = 'VALIDATED';
    case INVALID = 'INVALID';
}
```

## Models

### AppsFlyerEvent

**Responsibility**: Data access for AppsFlyer events

**Methods**:
- `create(array $data): int` - Create event record
- `findById(int $id): ?array` - Find by ID
- `findByUsuario(int $usuarioId): array` - Find by user
- `findByIndicacao(int $indicacaoId): array` - Find by indication
- `findByAppsflyerId(string $appsflyerId): ?array` - Find by AppsFlyer ID
- `findAll(array $filters): array` - Find with filters
- `updateStatus(int $id, string $status): void` - Update status
- `getStats(): array` - Get statistics
- `countByStatus(string $status): int` - Count by status
- `listRecent(int $limit): array` - List recent events

## Repositories

### AppsFlyerRepository

**Responsibility**: Abstract data access for AppsFlyer events

**Methods**:
- `findById(int $id): ?array`
- `findByUsuario(int $usuarioId): array`
- `findByIndicacao(int $indicacaoId): array`
- `findByAppsflyerId(string $appsflyerId): ?array`
- `findAll(array $filters): array`
- `create(array $data): int`
- `updateStatus(int $id, string $status): void`
- `getStats(): array`
- `countByStatus(string $status): int`
- `listRecent(int $limit): array`
- `existsByAppsflyerId(string $appsflyerId): bool`

## Services

### AppsFlyerService

**Responsibility**: Business logic for AppsFlyer events

**Methods**:
- `processEvent(array $payload): int` - Process incoming event
- `validateEvent(int $eventId): bool` - Validate event
- `rejectEvent(int $eventId, string $reason): bool` - Reject event
- `getEventByAppsflyerId(string $appsflyerId): ?array`
- `getEventsByUser(int $usuarioId): array`
- `getEventsByIndication(int $indicacaoId): array`
- `getAllEvents(array $filters): array`
- `getStats(): array`
- `isEnabled(): bool` - Check if integration is enabled

### AppsFlyerValidationService

**Responsibility**: Validate AppsFlyer data

**Methods**:
- `validatePayload(array $payload): array` - Validate payload structure
- `validateInstallation(array $payload): array` - Validate installation data
- `validatePlatform(string $platform): bool` - Validate platform
- `validateCampaign(?string $campaign): bool` - Validate campaign
- `validateOrigin(?string $mediaSource): bool` - Validate media source
- `validateEventValue(?string $eventValue): bool` - Validate event value
- `isDuplicateEvent(string $appsflyerId, string $eventName): bool` - Check duplicates
- `validateEventTiming(array $payload): bool` - Validate event timing
- `getValidationSummary(array $payload): array` - Get validation summary

### AppsFlyerWebhookValidator

**Responsibility**: Validate webhook requests (disabled)

**Status**: Disabled - prepared for future integration

**Methods**:
- `validate(array $payload, string $signature): bool`
- `isEnabled(): bool`

### AppsFlyerSignatureValidator

**Responsibility**: Validate webhook signatures (disabled)

**Status**: Disabled - prepared for future integration

**Methods**:
- `validate(string $payload, string $signature): bool`
- `isEnabled(): bool`

### AppsFlyerPayloadValidator

**Responsibility**: Validate payload structure (disabled)

**Status**: Disabled - prepared for future integration

**Methods**:
- `validate(array $payload): array`
- `isEnabled(): bool`

## Configuration

### config/appsflyer.php

```php
return [
    'enabled' => false,
    'api_key' => '',
    'dev_key' => '',
    'app_id_android' => '',
    'app_id_ios' => '',
    'onelink_template' => '',
    'endpoint' => '',
    'webhook' => [
        'enabled' => false,
        'secret' => '',
        'validate_signature' => false,
    ],
    'events' => [
        'install' => 'install',
        'first_open' => 'first_open',
        'purchase' => 'purchase',
        'custom_event' => 'custom_event',
    ],
    'validation' => [
        'require_media_source' => true,
        'require_campaign' => false,
        'allow_duplicate_events' => false,
        'event_ttl_hours' => 24,
    ],
    'retry' => [
        'max_attempts' => 3,
        'delay_seconds' => 5,
    ],
    'logging' => [
        'log_all_events' => true,
        'log_payloads' => true,
        'log_responses' => true,
    ],
];
```

## Controllers

### AppsFlyerController

**Responsibility**: Handle admin interface for AppsFlyer

**Methods**:
- `adminIndex(): void` - List all events with filters
- `view(int $id): void` - View event details
- `validate(): void` - Validate event (POST)
- `reject(): void` - Reject event (POST)

**Security**: Requires admin authentication

## Views

### admin/appsflyer.php

**Features**:
- Integration status indicator
- Statistics cards (total, pending, received, validated, invalid)
- Recent events list
- Filters (status, platform, install type, date range)
- All events list with actions
- Validate/reject buttons for pending events

### admin/appsflyer-view.php

**Features**:
- Event details
- Status badge
- Raw payload display
- Validate/reject actions

## Event Logging

### Events Logged

- `APPSFLYER_EVENTO_RECEBIDO` - Event received
- `APPSFLYER_EVENTO_PROCESSADO` - Event processed
- `APPSFLYER_EVENTO_VALIDADO` - Event validated
- `APPSFLYER_EVENTO_REJEITADO` - Event rejected

### EventLogger Methods

- `logAppsflyerEventoRecebido(int $eventId, string $eventName)`
- `logAppsflyerEventoProcessado(int $eventId, string $eventName)`
- `logAppsflyerEventoValidado(int $eventId, string $eventName)`
- `logAppsflyerEventoRejeitado(int $eventId, string $eventName, string $reason)`

## Routes

### Admin Routes

- `GET /admin/appsflyer` - List events
- `GET /admin/appsflyer/{id}` - View event details
- `POST /admin/appsflyer/validar` - Validate event
- `POST /admin/appsflyer/rejeitar` - Reject event

## Security

### Disabled Validators

The following security validators are prepared but currently disabled:

1. **AppsFlyerWebhookValidator** - Validates webhook requests
2. **AppsFlyerSignatureValidator** - Validates HMAC signatures
3. **AppsFlyerPayloadValidator** - Validates payload structure

### Future Implementation

When enabling AppsFlyer integration:

1. Configure credentials in `config/appsflyer.php`
2. Enable webhook validation
3. Implement signature validation using HMAC-SHA256
4. Implement payload structure validation
5. Enable webhook endpoint
6. Test with AppsFlyer sandbox

## Not Implemented (Future)

- Webhook endpoint
- AppsFlyer SDK integration
- Deep linking
- Deferred deep linking
- Installation tracking
- API calls to AppsFlyer

## Data Flow

### Event Processing Flow

1. Event received from AppsFlyer (future)
2. Payload validated (future)
3. Signature verified (future)
4. Event created in database (PENDING)
5. Event logged (APPSFLYER_EVENTO_RECEBIDO)
6. Event processed (RECEIVED)
7. Event logged (APPSFLYER_EVENTO_PROCESSADO)
8. Event validated (VALIDATED)
9. Event logged (APPSFLYER_EVENTO_VALIDADO)

### Manual Validation Flow (Admin)

1. Admin views event list
2. Admin clicks "Validar"
3. Event status updated to VALIDATED
4. Event logged (APPSFLYER_EVENTO_VALIDADO)

### Manual Rejection Flow (Admin)

1. Admin views event details
2. Admin enters rejection reason
3. Admin clicks "Rejeitar"
4. Event status updated to INVALID
5. Event logged (APPSFLYER_EVENTO_REJEITADO)

## Testing

### Manual Testing

1. Access `/admin/appsflyer`
2. Verify integration status is inactive
3. Verify statistics show 0
4. Verify no events displayed
5. Test filters (status, platform, install type, date range)

### Future Testing

When integrating with AppsFlyer:

1. Test webhook endpoint
2. Test signature validation
3. Test payload validation
4. Test event processing
5. Test duplicate detection
6. Test timing validation

## Troubleshooting

### Common Issues

**Integration shows inactive**
- Check `config/appsflyer.php`
- Verify `enabled` is set to `true`
- Configure API credentials

**Events not being processed**
- Check logs in `api_logs` table
- Verify event logging is enabled
- Check EventLogger configuration

**Validation failing**
- Check AppsFlyerValidationService
- Verify validation rules
- Check event payload structure

## Maintenance

### Regular Tasks

- Review pending events
- Validate/reject events as needed
- Monitor event statistics
- Check logs for errors

### Configuration Updates

- Update API credentials when needed
- Adjust validation rules
- Configure retry settings
- Update logging preferences

## References

- [AppsFlyer Documentation](https://support.appsflyer.com/)
- [AppsFlyer Webhooks](https://support.appsflyer.com/hc/en-us/articles/207032066-Webhooks)
- [AppsFlyer API](https://support.appsflyer.com/hc/en-us/articles/360002726579-AppsFlyer-API)
