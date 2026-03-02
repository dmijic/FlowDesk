# 📋 FlowDesk – Korisničke upute

> **FlowDesk** je interna web aplikacija koja zamjenjuje mailove i Excel tablice za sve vrste zahtjeva unutar tvrtke – IT pristupe, nabavu, putne naloge i slično. Svaki zahtjev ima jasan status, odobravanja po pravilima i potpunu povijest akcija.

---

## Sadržaj

1. [Uloge u sustavu](#1-uloge-u-sustavu)
2. [Kako se prijaviti i kretati aplikacijom](#2-kako-se-prijaviti-i-kretati-aplikacijom)
3. [Dashboard – pregled stanja](#3-dashboard--pregled-stanja)
4. [Kreiranje zahtjeva (Requester)](#4-kreiranje-zahtjeva-requester)
5. [Praćenje vlastitih zahtjeva](#5-praćenje-vlastitih-zahtjeva)
6. [Odobravanje zahtjeva (Approver)](#6-odobravanje-zahtjeva-approver)
7. [Upravljanje procesima (Process Owner)](#7-upravljanje-procesima-process-owner)
8. [Admin – upravljanje sustavom](#8-admin--upravljanje-sustavom)
9. [Notifikacije](#9-notifikacije)
10. [Mock scenariji – primjeri iz prakse](#10-mock-scenariji--primjeri-iz-prakse)

---

## 1. Uloge u sustavu

Svaki korisnik ima jednu ili više uloga. Od uloge ovisi što vidiš i što možeš raditi.

| Uloga             | Kratak opis                                                  |
| ----------------- | ------------------------------------------------------------ |
| **Requester**     | Kreira i prati vlastite zahtjeve                             |
| **Approver**      | Odobrava ili odbija zahtjeve koji su mu dodijeljeni          |
| **Process Owner** | Nadgleda procese i workflow definicije, dobiva obavijesti    |
| **Admin**         | Upravlja svim – korisnici, uloge, tipovi zahtjeva, reporting |

> 💡 Jedna osoba može imati više uloga istovremeno (npr. biti Requester i Approver).

---

## 2. Kako se prijaviti i kretati aplikacijom

### Prijava

1. Otvori FlowDesk u pregledniku (URL ti daje admin).
2. Unesi svoje korisničko ime i lozinku.
3. Klikni **Sign In**.

> Ako nemaš račun ili si zaboravio/la lozinku → kontaktiraj svog IT administratora.

### Navigacija

Nakon prijave vidjet ćeš bočni izbornik (sidebar) s glavnim sekcijama:

- 🏠 **Dashboard** – pregled na jednom mjestu
- 📄 **Requests** – kreiraj i prati zahtjeve
- ✅ **Approvals Inbox** – zadaci koji čekaju tvoje odobrenje
- 📊 **Reports** – izvještaji i export (Admin/Process Owner)
- ⚙️ **Settings** – korisnici, workflow definicije (Admin)
- 🔔 **Notifikacije** – zvonce u gornjem desnom kutu

---

## 3. Dashboard – pregled stanja

Dashboard je prva stvar koju vidiš nakon prijave. Na njemu su:

- **Brojevi zahtjeva po statusu:**
  - `Draft` – još nije poslan
  - `In Review` – čeka odobrenje
  - `Approved` – odobren
  - `Rejected` – odbijen
  - `Canceled` – otkazan

- **Prosječno vrijeme obrade** – koliko dugo traje od slanja do odluke

- **Najčešći tipovi zahtjeva** – koje kategorije se najčešće koriste

- **Brzi gumbi:**
  - `+ Create Request` – novi zahtjev
  - `Approvals Inbox` – tvoji zadaci
  - `Reports` – izvještaji

---

## 4. Kreiranje zahtjeva (Requester)

### Korak po korak

#### Korak 1 – Odabir tipa zahtjeva

1. Klikni na **"+ Create Request"** (iz Dashboarda ili iz menija Requests).
2. Prikazat će se lista dostupnih tipova zahtjeva, npr.:
   - **IT Access** – tražiš pristup sustavu ili alatu
   - **Procurement** – nabava opreme ili usluge
   - **Travel** – putni nalog
3. Klikni na željeni tip → klikni **"Next"**.

#### Korak 2 – Opis i prioritet

1. Unesi **naslov zahtjeva** (kratko, jasno – npr. _"Pristup GitHub repozitoriju – projekt Alpha"_).
2. Unesi **opis** – što točno trebaš, zašto, koji je kontekst.
3. Odaberi **prioritet:**
   - 🟢 `Low` – nije hitno
   - 🟡 `Medium` – standardno
   - 🔴 `High` – hitno, treba pažnju

#### Korak 3 – Odjel

1. Iz padajućeg izbornika odaberi **odjel** (department) koji se tiče zahtjeva.

#### Korak 4 – Privitci (opcionalno)

1. Klikni **"Upload"** ako trebaš priložiti dokument, sliku ili specifikaciju.
2. Možeš priložiti više fajlova.

> 🔒 Privitci su privatni – nisu javno vidljivi svima.

#### Korak 5 – Pregled i slanje

1. Pregledaj sve unesene podatke na ekranu **"Review & Submit"**.
2. Ako je sve OK, klikni **"Submit"**.

**Što se dogodi nakon slanja:**

- Status zahtjeva prelazi u `In Review`
- Sustav automatski šalje zadatak (ApprovalTask) odgovarajućim approverima
- Ti dobivaš potvrdu putem notifikacije

---

## 5. Praćenje vlastitih zahtjeva

### Kako otvoriti listu zahtjeva

1. U meniju klikni **"Requests"** → **"My Requests"**.
2. Vidjet ćeš sve svoje zahtjeve s kolonama: naslov, tip, status, datum.

### Filtriranje

Možeš filtrirati po:

- **Statusu** (Draft, In Review, Approved, Rejected...)
- **Tipu zahtjeva** (IT Access, Procurement...)
- **Datumu** (od – do)

### Detalji zahtjeva

Klikni na bilo koji zahtjev da otvoriš detalje. Vidjet ćeš:

| Sekcija              | Što prikazuje                                                |
| -------------------- | ------------------------------------------------------------ |
| **Osnovni podaci**   | Tip, odjel, prioritet, status                                |
| **Privitci**         | Fajlovi koje si uploadao/la                                  |
| **Timeline**         | Sva događanja – kada je kreiran, poslan, tko je što napravio |
| **Approval sekcija** | Koji je trenutni korak odobravanja, tko odobrava             |

---

## 6. Odobravanje zahtjeva (Approver)

### Pristup Inboxu

1. Klikni na **"Approvals Inbox"** u meniju.
2. Vidjet ćeš listu svih zadataka (taskova) koji čekaju tvoju odluku.

Za svaki zadatak prikazuje se:

- Naslov zahtjeva
- Naziv koraka (npr. _"Manager Approval"_)
- Prioritet
- Kada je zahtjev poslan

### Kako odobriti zahtjev

1. Klikni na zadatak da otвориš detalje.
2. Pregledaj sve informacije (opis, privitke, timeline).
3. Klikni **"Approve"**.
4. Možeš (opcionalno) dodati komentar uz odobrenje.
5. Klikni **"Confirm"**.

### Kako odbiti zahtjev

1. Klikni na zadatak → pregledaj detalje.
2. Klikni **"Reject"**.
3. Unesi **komentar** – zašto odbijate (obično obavezno).
4. Klikni **"Confirm"**.

> ⚠️ Odbijanje odmah zatvara cijeli zahtjev – status postaje `Rejected` i requester dobiva notifikaciju.

### Pravila odobravanja

| Pravilo      | Kako funkcionira                                                                               |
| ------------ | ---------------------------------------------------------------------------------------------- |
| `rule = any` | Dovoljan je **jedan** approve da korak prođe. Ostali taskovi tog koraka se automatski zatvore. |
| `rule = all` | **Svi** dodijeljeni approveri moraju odobriti. Ako itko odbije → zahtjev je odbijen.           |

---

## 7. Upravljanje procesima (Process Owner)

Process Owner nadgleda procese i dobiva obavijesti o svim odlukama.

### Što možeš raditi

- **Pregledavati workflow definicije** – koji su koraci za svaki tip zahtjeva
- **Analizirati gdje proces zapinje** – npr. koji korak traje najdulje
- **Primati notifikacije** o svim odobrenjima i odbijanjima

### Workflow definicije

Workflow je niz koraka koji se prolaze za određeni tip zahtjeva. Primjer za **IT Access**:

```
Korak 1: Manager Approval  (rule: any)
Korak 2: Security Review   (rule: all)
Korak 3: IT Admin Confirm  (rule: any)
```

Svaki korak definira:

- **Tko odobrava** – konkretni korisnici ili uloga (npr. svi s ulogom _Security_)
- **Redoslijed** – paralelno (svi odjednom) ili sekvencijalno (jedan po jedan)
- **Pravilo** – `any` ili `all`

---

## 8. Admin – upravljanje sustavom

### Upravljanje korisnicima

**Kreiranje novog korisnika:**

1. Idi na **Settings → Users**.
2. Klikni **"+ New User"**.
3. Unesi ime, e-mail, odjel.
4. Dodijeli ulogu: Requester / Approver / Process Owner / Admin.
5. Klikni **"Save"** → korisnik dobiva pozivnicu na mail.

**Uređivanje korisnika:**

1. Pronađi korisnika u listi.
2. Klikni na njega → uredi podatke, promijeni uloge.
3. **"Save"**.

### Workflow definicije

1. Idi na **Settings → Workflow Definitions**.
2. Odaberi tip zahtjeva (npr. _Procurement_).
3. Dodaj ili uredi korake:
   - Klikni **"+ Add Step"**
   - Definiraj: naziv koraka, tko odobrava, pravilo (any/all), redoslijed
4. Klikni **"Save Workflow"**.

### Reports i export

1. Idi na **Reports**.
2. Pregledaj:
   - Broj zahtjeva po statusu i tipu
   - Prosječno vrijeme obrade
   - Top tipove zahtjeva
3. Klikni **"Export CSV"** da preuzmeš podatke za Excel/BI alate.

---

## 9. Notifikacije

### In-app obavijesti

- Klikni na 🔔 **zvonce** u gornjem desnom kutu.
- Vidjet ćeš listu svih obavijesti (npr. _"Tvoj zahtjev je odobren"_, _"Novi task čeka tvoje odobrenje"_).

### E-mail obavijesti

- Ovisno o konfiguraciji, određeni eventi šalju i e-mail obavijesti.
- Tipično dobivaš mail kada:
  - Ti je dodijeljen zadatak za odobrenje
  - Tvoj zahtjev je odobren ili odbijen

---

## 10. Mock scenariji – primjeri iz prakse

---

### 📌 Scenarij 1: IT Access – Tražim pristup GitHub repozitoriju

**Uloge:** Ana (Requester) → Marko (Manager/Approver) → IT Tim (Security Approver)

**Workflow za IT Access:**

```
Korak 1: Manager Approval  (rule: any, paralelno nije bitno - jedan approver)
Korak 2: Security Review   (rule: all, svi security approveri moraju odobriti)
```

#### Ana kreira zahtjev

1. Ana se prijavi u FlowDesk.
2. Klikne **"+ Create Request"**.
3. Odabere tip: **IT Access**.
4. Unese:
   - Naslov: _"Pristup GitHub repozitoriju – projekt Alpha"_
   - Opis: _"Trebam pristup repozitoriju alpha-backend za razvoj feature brancha. Projekt počinje 15.3."_
   - Prioritet: `Medium`
   - Odjel: _Razvoj softvera_
5. Uploadá specifikaciju projekta (PDF).
6. Klikne **"Submit"**.

✅ **Rezultat:** Status → `In Review`. Marku stiže notifikacija: _"Novi zahtjev čeka tvoje odobrenje."_

---

#### Marko odobrava (Korak 1)

1. Marko otvori **Approvals Inbox**.
2. Vidi zadatak: _"Pristup GitHub repozitoriju – projekt Alpha | Manager Approval | Medium"_
3. Klikne na zadatak, pročita detalje i Anin privitak.
4. Klikne **"Approve"**, dodaje komentar: _"Ana je na projektu, pristup opravdan."_
5. Klikne **"Confirm"**.

✅ **Rezultat:** Korak 1 je prošao. Sustav automatski kreira taskove za IT Security tim (Korak 2).

---

#### IT Security tim odobrava (Korak 2 – rule: all)

IT Security tim ima dva člana: Ivan i Petra. **Oba moraju odobriti.**

**Ivan:**

1. Otvori Inbox → pronađe Anin zahtjev.
2. Klikne **"Approve"** bez komentara.

**Petra:**

1. Otvori Inbox → pronađe isti zahtjev.
2. Klikne **"Approve"** uz komentar: _"Provjerila pristupe, sve OK."_

✅ **Rezultat:** Oba su odobrila → zahtjev postaje `Approved`. Ana dobiva notifikaciju.

---

**Ana prati status:**

- Idi na **My Requests** → klikni na zahtjev.
- U **Timeline** vidi: kreiran → poslan → Manager odobrio → Security odobrio → **Approved**.

---

### 📌 Scenarij 2: Procurement – Nabava laptopa

**Uloge:** Tomislav (Requester) → Maja (Manager/Approver) → Finance (Approver, rule: any)

**Workflow za Procurement:**

```
Korak 1: Manager Approval  (rule: any)
Korak 2: Finance Approval  (rule: any – dovoljan je jedan finance approver)
```

#### Tomislav kreira zahtjev

1. Klikne **"+ Create Request"** → odabere **Procurement**.
2. Unese:
   - Naslov: _"Nabava laptopa – zamjena starog uređaja"_
   - Opis: _"Stari laptop (2018) ne može pokrenuti razvojne alate. Predlažem MacBook Pro 14" M3. Cijena: ~2.500 EUR."_
   - Prioritet: `High`
   - Odjel: _Razvoj softvera_
3. Prilaže ponudu dobavljača (PDF).
4. Klikne **"Submit"**.

✅ **Notifikacija Maji:** _"Novi zahtjev za odobrenje: Nabava laptopa"_

---

#### Maja odbija (Korak 1)

1. Maja otvori Inbox → klikne na zahtjev.
2. Vidi zahtjev, ali smatra da je cijena previsoka za Q1 budžet.
3. Klikne **"Reject"**.
4. Unese komentar: _"Budžet za Q1 je iskorišten. Preporuči jeftiniju opciju ili pričekaj Q2."_
5. Klikne **"Confirm"**.

✅ **Rezultat:** Status → `Rejected`. Tomislav dobiva notifikaciju s Majinim komentarom.

**Tomislav može:**

- Pročitati razlog u **Timeline** sekciji zahtjeva.
- Kreirati **novi zahtjev** s jeftinijom opcijom ili zaplanirati za Q2.

---

### 📌 Scenarij 3: Travel – Putni nalog za konferenciju

**Uloge:** Sandra (Requester) → Direktor (Approver, rule: any) → Finance (Approver, rule: any)

**Workflow za Travel:**

```
Korak 1: Director Approval (rule: any)
Korak 2: Finance Approval  (rule: any)
```

#### Sandra kreira zahtjev

1. Klikne **"+ Create Request"** → odabere **Travel**.
2. Unese:
   - Naslov: _"Putni nalog – React Europe konferencija, Pariz, 5-7.6.2025."_
   - Opis: _"Konferencija o modernim React tehnikama. Trošak: let ~300 EUR, hotel 3 noći ~450 EUR, ukupno ~750 EUR."_
   - Prioritet: `Medium`
   - Odjel: _Frontend tim_
3. Prilaže program konferencije.
4. Klikne **"Submit"**.

---

#### Direktor odobrava (Korak 1)

1. Otvori Inbox → vidi Sandrin zahtjev.
2. Klikne **"Approve"** uz komentar: _"Korisna konferencija, odobravamo."_

---

#### Finance odobrava (Korak 2)

1. Finance manager Luka otvori Inbox.
2. Pregleda troškove, provjeri budžet.
3. Klikne **"Approve"** uz komentar: _"Budžet za edukaciju dostatan, odobreno."_

✅ **Rezultat:** Status → `Approved`. Sandra dobiva sve notifikacije i može skinuti zahtjev kao potvrdu.

---

### 📌 Scenarij 4: Admin kreira novog korisnika i postavlja workflow

**Situacija:** U tvrtku dolazi novi zaposlenik Filip (developer). Admin ga treba dodati i postaviti mu uloge.

#### Kreiranje korisnika

1. Admin se prijavi i ode na **Settings → Users**.
2. Klikne **"+ New User"**.
3. Unese:
   - Ime: _Filip Horvat_
   - E-mail: *filip.horvat@tvrtka.hr*
   - Odjel: _Razvoj softvera_
   - Uloge: `Requester`
4. Klikne **"Save"**.

✅ Filip dobiva e-mail s pozivnicom za postavljanje lozinke.

---

#### Postavljanje novog workflow koraka za IT Access

Tvrtka odluči dodati novi korak "CISO Approval" za sve IT Access zahtjeve koji imaju prioritet High.

1. Admin ode na **Settings → Workflow Definitions**.
2. Klikne na **IT Access**.
3. Klikne **"+ Add Step"** između koraka 1 i 2.
4. Postavi:
   - Naziv: _"CISO Approval"_
   - Approver: korisnik *ciso@tvrtka.hr*
   - Pravilo: `any`
   - Redoslijed: sekvencijalno (nakon Manager Approval)
5. Klikne **"Save Workflow"**.

✅ Svi novi zahtjevi IT Access tipa sada prolaze kroz 3 koraka.

---

## 🔑 Brzi podsjetnik – Statusi zahtjeva

| Status      | Što znači                                    |
| ----------- | -------------------------------------------- |
| `Draft`     | Zahtjev je kreiran ali još nije poslan       |
| `In Review` | Zahtjev čeka odobrenje (u procesu)           |
| `Approved`  | Svi koraci su odobreni ✅                    |
| `Rejected`  | Netko je odbio – vidi komentar u Timeline ❌ |
| `Canceled`  | Zahtjev je otkazan (requester ili admin)     |

---

## ❓ Česta pitanja

**Mogu li uređivati zahtjev nakon slanja?**
Ne. Zahtjev koji je u statusu `In Review` ne može se uređivati. Ako trebaš promjenu, kontaktiraj approvera ili otkaži i kreiraj novi.

**Što ako approver ne reagira?**
Kontaktiraj svog Process Ownera ili admina koji može eskalirate ili prerasporediti zadatak.

**Gdje vidim komentare approvera?**
U detaljima zahtjeva → sekcija **Timeline** i **Approval sekcija**.

**Mogu li preuzeti privitke?**
Da, ako imaš pravo preuzimanja. Idi na detalj zahtjeva → Privitci → klikni ikonu za preuzimanje.

**Kako znam da je moj zahtjev odobren/odbijen?**
Dobivaš in-app notifikaciju (🔔 zvonce) i e-mail obavijest.

---

_FlowDesk – Digitalizirajmo procese, ne mailove._ 🚀
