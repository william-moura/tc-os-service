# TC Work Order Service (`tc-os-service`)

Microsserviço responsável pela gestão do ciclo de vida das Ordens de Serviço (Work Orders) da oficina mecânica, integrando o ecossistema distribuído da Fase 4 do Tech Challenge.

---

## 🏛️ Arquitetura e Padrões de Projeto

O serviço foi desenhado seguindo os princípios de **Domain-Driven Design (DDD)** e arquitetura hexagonal/limpa, isolando as regras de negócio de quaisquer dependências de infraestrutura ou de frameworks:

* **Domain Layer (`app/Domain`)**: Contém entidades puras (`WorkOrder`, `ServiceItem`), objetos de valor com validações invariantes (`VehiclePlate`, `CustomerId`, `WorkOrderId`, `WorkOrderStatus`), contratos de repositório e eventos de domínio.
* **Application Layer (`app/Application`)**: Orquestra os fluxos através de Casos de Uso (`CreateWorkOrderUseCase`, `UpdateWorkOrderStatusUseCase`, `CancelWorkOrderUseCase`, `GetWorkOrderUseCase`) e DTOs tipados.
* **Infrastructure Layer (`app/Infrastructure`)**: Implementa persistência via Eloquent ORM (MySQL), mapeadores desacoplados (`WorkOrderMapper`) e adaptadores de mensageria AMQP via RabbitMQ.
* **Presentation Layer (`app/Presentation`)**: Fornece controladores REST HTTP com validação de pedidos (`FormRequest`), serialização JSON (`WorkOrderResource`) e comandos CLI para execução contínua de _consumers_.

---

## 🔄 Fluxo da Saga Pattern (Coreografia)

Este microsserviço atua como ponto de partida e de conclusão da Saga transacional das Ordens de Serviço: