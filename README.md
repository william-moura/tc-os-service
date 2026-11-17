# TC Work Order Service (`tc-os-service`)

Microsserviço responsável pela gestão do ciclo de vida das Ordens de Serviço (Work Orders) da oficina mecânica, integrando o ecossistema distribuído da **Fase 4 do Tech Challenge**.

---

## 🏛️ Arquitetura e Padrões de Projeto (DDD)

O serviço foi estruturado seguindo rigorosamente os princípios de **Domain-Driven Design (DDD)** e arquitetura em camadas independentes, garantindo desacoplamento total das regras de negócio em relação ao framework e banco de dados:

```
app/
├── Domain/                 # Regras puras de negócio (Zero dependência externa)
│   ├── Entities/           # WorkOrder (Agregado raiz), ServiceItem
│   ├── ValueObjects/       # VehiclePlate, CustomerId, WorkOrderId, WorkOrderStatus
│   ├── Repositories/       # WorkOrderRepositoryInterface (Contrato de persistência)
│   ├── Events/             # WorkOrderCreatedDomainEvent, WorkOrderCanceledDomainEvent
│   └── Exceptions/         # InvalidStatusTransitionException, WorkOrderNotFoundException
│
├── Application/            # Casos de uso e orquestração de fluxos
│   ├── UseCases/           # CreateWorkOrderUseCase, UpdateWorkOrderStatusUseCase, CancelWorkOrderUseCase, GetWorkOrderUseCase
│   ├── DTOs/               # CreateWorkOrderInputDto, ServiceItemDto, WorkOrderOutputDto
│   └── Services/           # MessagePublisherInterface (Contrato de mensageria)
│
├── Infrastructure/         # Detalhes de implementação técnica
│   ├── Persistence/        # Models Eloquent (WorkOrderModel, ServiceItemModel)
│   ├── Mappers/            # WorkOrderMapper (Conversor Model <-> Domínio)
│   ├── Repositories/       # WorkOrderRepository (Implementação MySQL/Eloquent)
│   └── Messaging/          # RabbitMQService, Publishers e Consumers (Rollback Saga)
│
└── Presentation/           # Portas de entrada
    ├── Http/               # WorkOrderController, CreateWorkOrderRequest, WorkOrderResource
    └── Console/Commands/   # StartRabbitMQConsumers (Worker CLI)
```

---

## 🔄 Saga Pattern (Coreografia de Transações Distribuídas)

A coordenação transacional entre os microsserviços é baseada no padrão **Saga Coreografada** via RabbitMQ, garantindo consistência eventual e mecanismos seguros de rollback (compensação):

```
[Cliente / API]
       │
       ▼  POST /api/v1/work-orders
┌──────────────────┐
│  tc-os-service   │ ──(Persiste OS no MySQL com status 'CREATED')
└─────────┬────────┘
          │  Publica evento: `queue.workorder.created`
          ▼
┌──────────────────┐
│tc-billing-service│ ──(Gera orçamento no MongoDB e checkout Mercado Pago)
└─────────┬────────┘
          │
          ├── [Falha no Pagamento / Recusa]
          │     └── Publica evento: `queue.payment.failed`
          │           ▼
          │     ┌──────────────────┐
          │     │  tc-os-service   │ ──(Consome evento: transita OS para 'CANCELED' [Rollback])
          │     └──────────────────┘
          │
          └── [Pagamento Aprovado]
                └── Publica evento: `queue.payment.approved`
                      ▼
                ┌────────────────────────┐
                │ tc-production-service  │ ──(Aloca OS na esteira de produção técnica)
                └───────────┬────────────┘
                            │  [Reparo Concluído]
                            │  Publica evento: `queue.repair.completed`
                            ▼
                ┌────────────────────────┐
                │     tc-os-service      │ ──(Consome evento: transita OS para 'COMPLETED')
                └────────────────────────┘
```

### Filas e Eventos Relacionados
* **Publicados pelo `tc-os-service`**:
  * `queue.workorder.created`: Informa a criação da OS com identificador, cliente e itens para cálculo de faturação.
* **Consumidos pelo `tc-os-service`**:
  * `queue.payment.failed`: Dispara o `CancelWorkOrderUseCase`, executando a compensação e atualizando o status para `CANCELED`.
  * `queue.repair.completed`: Dispara o `UpdateWorkOrderStatusUseCase`, finalizando o ciclo de vida com o status `COMPLETED`.

---

## 🛠️ Stack Tecnológica

* **Linguagem**: PHP 8.4
* **Framework**: Laravel 10
* **Banco de Dados**: MySQL 8.0 (Banco Relacional / SQL obrigatório)
* **Mensageria**: RabbitMQ 3 (AMQP 0-9-1)
* **Qualidade de Código**: PHPUnit (cobertura > 80%), SonarQube

---

## 🚀 Como Executar Localmente

### 1. Clonar o repositório e configurar variáveis de ambiente
```bash
cp .env.example .env
```

Configurações obrigatórias no `.env`:
```env
APP_NAME=tc-os-service
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=tc_workorders
DB_USERNAME=root
DB_PASSWORD=secret

RABBITMQ_HOST=rabbitmq
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest
RABBITMQ_VHOST=/
```

### 2. Inicializar o ambiente com Docker Compose
```bash
docker compose up -d --build
```

### 3. Instalar dependências e rodar migrations
```bash
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

### 4. Executar os Consumers da Saga
Em terminais dedicados (ou via supervisor/worker pod no Kubernetes):
```bash
# Escutar eventos de falha e compensação (Rollback)
docker compose exec app php artisan rabbitmq:consume payment-failed

# Escutar eventos de reparo concluído
docker compose exec app php artisan rabbitmq:consume repair-completed
```

---

## 🧪 Testes Automatizados e Cobertura

O projeto possui cobertura unitária e de integração superior a 80%, focando no isolamento das regras de domínio e dos casos de uso:

```bash
# Executar todos os testes
php artisan test

# Executar com validação de cobertura mínima (80%)
php artisan test --coverage-text --min=80
```

---

## 📚 Documentação da API REST

### 1. Criar Ordem de Serviço
* **Método**: `POST`
* **Rota**: `/api/v1/work-orders`
* **Headers**: `Content-Type: application/json`, `Accept: application/json`

**Payload de Requisição**:
```json
{
  "customer_id": 10,
  "vehicle_plate": "BRA2E19",
  "notes": "Cliente relata ruído no sistema de freios.",
  "items": [
    {
      "description": "Pastilhas de Freio Dianteiras",
      "quantity": 2,
      "unit_price": 75.50
    },
    {
      "description": "Mão de Obra Mecânica",
      "quantity": 1,
      "unit_price": 120.00
    }
  ]
}
```

**Resposta de Sucesso (`201 Created`)**:
```json
{
  "id": 1,
  "customer_id": 10,
  "vehicle_plate": "BRA2E19",
  "status": "CREATED",
  "total_amount": 271.00,
  "notes": "Cliente relata ruído no sistema de freios."
}
```

---

### 2. Consultar Ordem de Serviço por ID
* **Método**: `GET`
* **Rota**: `/api/v1/work-orders/{id}`
* **Headers**: `Accept: application/json`

**Resposta de Sucesso (`200 OK`)**:
```json
{
  "id": 1,
  "customer_id": 10,
  "vehicle_plate": "BRA2E19",
  "status": "AWAITING_PAYMENT",
  "total_amount": 271.00,
  "notes": "Cliente relata ruído no sistema de freios.",
  "items": [
    {
      "id": 1,
      "description": "Pastilhas de Freio Dianteiras",
      "quantity": 2,
      "unit_price": 75.50,
      "total": 151.00
    },
    {
      "id": 2,
      "description": "Mão de Obra Mecânica",
      "quantity": 1,
      "unit_price": 120.00,
      "total": 120.00
    }
  ],
  "created_at": "2026-11-10T12:00:00+00:00",
  "updated_at": "2026-11-10T12:05:00+00:00"
}
```

**Resposta de Não Encontrado (`404 Not Found`)**:
```json
{
  "message": "Work order with ID 999 not found."
}
```