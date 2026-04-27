# VPC Peering entre dos cuentas AWS

## Lo que necesitamos antes de empezar

Cada uno debe tener preparado:

- **Account ID** → arriba a la derecha en la consola AWS
- **VPC ID** → VPC → Your VPCs

---

## PASO 1 — Creamos la VPC (cada uno en su cuenta)

**Luka (OPNsense) — Cuenta A**

Vamos a VPC → Your VPCs → Create VPC

```
Resources: VPC only
Name:      vpc-opnsense
IPv4 CIDR: 10.1.0.0/16
```

Damos a Create VPC

---

**Compañero — Cuenta B**

Vamos a VPC → Your VPCs → Create VPC

```
Resources: VPC only
Name:      vpc-proyecto
IPv4 CIDR: 10.2.0.0/16
```

Damos a Create VPC

---

## PASO 2 — Creamos la Subnet (cada uno en su cuenta)

**Luka**

Vamos a VPC → Subnets → Create Subnet

```
VPC:   vpc-opnsense
Name:  subnet-opnsense
AZ:    us-east-1a
CIDR:  10.1.1.0/24
```

Create Subnet

---

**Compañero**

Vamos a VPC → Subnets → Create Subnet

```
VPC:   vpc-proyecto
Name:  subnet-proyecto
AZ:    us-east-1a
CIDR:  10.2.1.0/24
```

Create Subnet

---

## PASO 3 — Creamos el Internet Gateway (cada uno en su cuenta)

**Luka**

```
VPC → Internet Gateways → Create
Name: igw-opnsense → Create
Actions → Attach to VPC → seleccionamos vpc-opnsense
```

**Compañero**

```
VPC → Internet Gateways → Create
Name: igw-proyecto → Create
Actions → Attach to VPC → seleccionamos vpc-proyecto
```

---

## PASO 4 — Creamos la Route Table (cada uno en su cuenta)

**Luka**

```
VPC → Route Tables → Create route table
Name: rt-opnsense | VPC: vpc-opnsense → Create
```

Pestaña Routes → Edit routes → Add route:

```
Destination: 0.0.0.0/0
Target:      igw-opnsense
```

Save

Pestaña Subnet associations → Edit → marcamos subnet-opnsense → Save

---

**Compañero**

```
VPC → Route Tables → Create route table
Name: rt-proyecto | VPC: vpc-proyecto → Create
```

Pestaña Routes → Edit routes → Add route:

```
Destination: 0.0.0.0/0
Target:      igw-proyecto
```

Save

Pestaña Subnet associations → Edit → marcamos subnet-proyecto → Save

---

## PASO 5 — Creamos el VPC Peering

**Luka (iniciamos la solicitud)**

```
VPC → Peering Connections → Create Peering Connection
Name:       peering-g7
Local VPC:  vpc-opnsense
Account:    Another account → introducimos el Account ID del compañero
Region:     us-east-1 (la misma)
VPC ID:     introducimos el VPC ID del compañero (vpc-proyecto)
```

Create → queda en estado **Pending Acceptance**

---

**Compañero (acepta la solicitud)**

```
VPC → Peering Connections
```

Ve la solicitud en estado Pending → Actions → Accept Request → Confirma

---

## PASO 6 — Añadimos las rutas del Peering (los dos)

**Luka**

```
Route Tables → rt-opnsense → Routes → Edit routes → Add route:
Destination: 10.2.0.0/16
Target:      pcx-xxxxxxxxx (el ID del peering)
```

Save

---

**Compañero**

```
Route Tables → rt-proyecto → Routes → Edit routes → Add route:
Destination: 10.1.0.0/16
Target:      pcx-xxxxxxxxx (el mismo ID del peering)
```

Save

---

## PASO 7 — Verificamos la conectividad

Desde cualquier instancia en la red de uno, hacemos ping a una instancia del otro:

```bash
# Desde la instancia de Luka → ping a una máquina del compañero
ping 10.2.1.X

# Desde una máquina del compañero → ping a OPNsense
ping 10.1.1.X
```
