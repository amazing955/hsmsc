--
-- PostgreSQL database dump
--

\restrict Jo45P0qKSdRt7NyoH4vmg9kAJmaspgYeERp3RrnrHBMqbou9xIkqYbZ8UNMMJaz

-- Dumped from database version 16.9 (415ebe8)
-- Dumped by pg_dump version 16.10

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: bookings; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.bookings (
    id integer NOT NULL,
    user_id integer NOT NULL,
    room_id integer NOT NULL,
    booking_date date NOT NULL,
    check_in date NOT NULL,
    check_out date NOT NULL,
    status character varying(20) DEFAULT 'pending'::character varying,
    total_amount numeric(10,2) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT bookings_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'confirmed'::character varying, 'cancelled'::character varying, 'completed'::character varying])::text[])))
);


ALTER TABLE public.bookings OWNER TO neondb_owner;

--
-- Name: bookings_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.bookings_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.bookings_id_seq OWNER TO neondb_owner;

--
-- Name: bookings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.bookings_id_seq OWNED BY public.bookings.id;


--
-- Name: feedback; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.feedback (
    id integer NOT NULL,
    user_id integer NOT NULL,
    subject character varying(200),
    message text NOT NULL,
    type character varying(20) DEFAULT 'feedback'::character varying,
    status character varying(20) DEFAULT 'pending'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT feedback_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'reviewed'::character varying, 'resolved'::character varying])::text[]))),
    CONSTRAINT feedback_type_check CHECK (((type)::text = ANY ((ARRAY['feedback'::character varying, 'complaint'::character varying, 'suggestion'::character varying])::text[])))
);


ALTER TABLE public.feedback OWNER TO neondb_owner;

--
-- Name: feedback_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.feedback_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.feedback_id_seq OWNER TO neondb_owner;

--
-- Name: feedback_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.feedback_id_seq OWNED BY public.feedback.id;


--
-- Name: hostels; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.hostels (
    id integer NOT NULL,
    name character varying(200) NOT NULL,
    location character varying(255) NOT NULL,
    latitude numeric(10,8),
    longitude numeric(11,8),
    description text,
    contact character varying(20),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    owner_id integer
);


ALTER TABLE public.hostels OWNER TO neondb_owner;

--
-- Name: hostels_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.hostels_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.hostels_id_seq OWNER TO neondb_owner;

--
-- Name: hostels_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.hostels_id_seq OWNED BY public.hostels.id;


--
-- Name: order_items; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.order_items (
    id integer NOT NULL,
    order_id integer NOT NULL,
    product_id integer NOT NULL,
    quantity integer NOT NULL,
    price numeric(10,2) NOT NULL
);


ALTER TABLE public.order_items OWNER TO neondb_owner;

--
-- Name: order_items_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.order_items_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.order_items_id_seq OWNER TO neondb_owner;

--
-- Name: order_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.order_items_id_seq OWNED BY public.order_items.id;


--
-- Name: orders; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.orders (
    id integer NOT NULL,
    user_id integer NOT NULL,
    total_amount numeric(10,2) NOT NULL,
    status character varying(20) DEFAULT 'pending'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT orders_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'processing'::character varying, 'completed'::character varying, 'cancelled'::character varying])::text[])))
);


ALTER TABLE public.orders OWNER TO neondb_owner;

--
-- Name: orders_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.orders_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.orders_id_seq OWNER TO neondb_owner;

--
-- Name: orders_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.orders_id_seq OWNED BY public.orders.id;


--
-- Name: products; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.products (
    id integer NOT NULL,
    name character varying(200) NOT NULL,
    description text,
    price numeric(10,2) NOT NULL,
    stock integer DEFAULT 0,
    image character varying(255),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    owner_id integer
);


ALTER TABLE public.products OWNER TO neondb_owner;

--
-- Name: products_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.products_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.products_id_seq OWNER TO neondb_owner;

--
-- Name: products_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.products_id_seq OWNED BY public.products.id;


--
-- Name: riders; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.riders (
    id integer NOT NULL,
    user_id integer NOT NULL,
    license_plate character varying(20) NOT NULL,
    bike_type character varying(50) DEFAULT 'Motorcycle'::character varying,
    phone character varying(20) NOT NULL,
    location character varying(255),
    is_available boolean DEFAULT true,
    rating numeric(2,1) DEFAULT 5.0,
    total_rides integer DEFAULT 0,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.riders OWNER TO neondb_owner;

--
-- Name: riders_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.riders_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.riders_id_seq OWNER TO neondb_owner;

--
-- Name: riders_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.riders_id_seq OWNED BY public.riders.id;


--
-- Name: room_layout; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.room_layout (
    id integer NOT NULL,
    user_id integer NOT NULL,
    layout_name character varying(100),
    layout_json text NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.room_layout OWNER TO neondb_owner;

--
-- Name: room_layout_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.room_layout_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.room_layout_id_seq OWNER TO neondb_owner;

--
-- Name: room_layout_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.room_layout_id_seq OWNED BY public.room_layout.id;


--
-- Name: rooms; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.rooms (
    id integer NOT NULL,
    hostel_id integer NOT NULL,
    room_number character varying(20) NOT NULL,
    room_type character varying(20) NOT NULL,
    capacity integer NOT NULL,
    price numeric(10,2) NOT NULL,
    availability boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT rooms_room_type_check CHECK (((room_type)::text = ANY ((ARRAY['Single'::character varying, 'Double'::character varying, 'Triple'::character varying])::text[])))
);


ALTER TABLE public.rooms OWNER TO neondb_owner;

--
-- Name: rooms_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.rooms_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.rooms_id_seq OWNER TO neondb_owner;

--
-- Name: rooms_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.rooms_id_seq OWNED BY public.rooms.id;


--
-- Name: transport; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.transport (
    id integer NOT NULL,
    user_id integer NOT NULL,
    pickup_location character varying(255) NOT NULL,
    destination character varying(255) NOT NULL,
    rider_name character varying(100),
    cost numeric(10,2) NOT NULL,
    status character varying(20) DEFAULT 'pending'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    rider_id integer,
    CONSTRAINT transport_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'assigned'::character varying, 'completed'::character varying, 'cancelled'::character varying])::text[])))
);


ALTER TABLE public.transport OWNER TO neondb_owner;

--
-- Name: transport_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.transport_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.transport_id_seq OWNER TO neondb_owner;

--
-- Name: transport_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.transport_id_seq OWNED BY public.transport.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.users (
    id integer NOT NULL,
    name character varying(100) NOT NULL,
    email character varying(100) NOT NULL,
    password character varying(255) NOT NULL,
    role character varying(20) DEFAULT 'student'::character varying,
    phone character varying(20),
    hostel_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT users_role_check CHECK (((role)::text = ANY ((ARRAY['admin'::character varying, 'student'::character varying, 'hostel_owner'::character varying, 'hotel_owner'::character varying, 'boda_rider'::character varying])::text[])))
);


ALTER TABLE public.users OWNER TO neondb_owner;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.users_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO neondb_owner;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: bookings id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.bookings ALTER COLUMN id SET DEFAULT nextval('public.bookings_id_seq'::regclass);


--
-- Name: feedback id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.feedback ALTER COLUMN id SET DEFAULT nextval('public.feedback_id_seq'::regclass);


--
-- Name: hostels id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.hostels ALTER COLUMN id SET DEFAULT nextval('public.hostels_id_seq'::regclass);


--
-- Name: order_items id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.order_items ALTER COLUMN id SET DEFAULT nextval('public.order_items_id_seq'::regclass);


--
-- Name: orders id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.orders ALTER COLUMN id SET DEFAULT nextval('public.orders_id_seq'::regclass);


--
-- Name: products id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.products ALTER COLUMN id SET DEFAULT nextval('public.products_id_seq'::regclass);


--
-- Name: riders id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.riders ALTER COLUMN id SET DEFAULT nextval('public.riders_id_seq'::regclass);


--
-- Name: room_layout id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.room_layout ALTER COLUMN id SET DEFAULT nextval('public.room_layout_id_seq'::regclass);


--
-- Name: rooms id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.rooms ALTER COLUMN id SET DEFAULT nextval('public.rooms_id_seq'::regclass);


--
-- Name: transport id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.transport ALTER COLUMN id SET DEFAULT nextval('public.transport_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Data for Name: bookings; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.bookings (id, user_id, room_id, booking_date, check_in, check_out, status, total_amount, created_at) FROM stdin;
1	2	7	2025-11-25	2025-11-25	2025-11-26	pending	360000.00	2025-11-25 20:38:14.127691
\.


--
-- Data for Name: feedback; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.feedback (id, user_id, subject, message, type, status, created_at) FROM stdin;
\.


--
-- Data for Name: hostels; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.hostels (id, name, location, latitude, longitude, description, contact, created_at, owner_id) FROM stdin;
1	Sunrise Hostel	Kampala, Uganda	0.34760000	32.58250000	Modern hostel with great amenities	+256700000001	2025-11-25 20:10:45.816358	\N
2	Moonlight Residence	Kampala, Uganda	0.35000000	32.58500000	Affordable and comfortable accommodation	+256700000002	2025-11-25 20:10:45.816358	\N
3	University Hostel	Kampala, Uganda	0.34500000	32.58000000	Close to universities and colleges	+256700000003	2025-11-25 20:10:45.816358	\N
4	Green Valley Hostel	Kampala, Uganda	0.34900000	32.58700000	Peaceful environment for students	+256700000004	2025-11-25 20:10:45.816358	\N
\.


--
-- Data for Name: order_items; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.order_items (id, order_id, product_id, quantity, price) FROM stdin;
\.


--
-- Data for Name: orders; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.orders (id, user_id, total_amount, status, created_at) FROM stdin;
\.


--
-- Data for Name: products; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.products (id, name, description, price, stock, image, created_at, owner_id) FROM stdin;
1	Instant Noodles	Quick and delicious instant noodles	2000.00	100	noodles.jpg	2025-11-25 20:10:45.816358	\N
2	Bottled Water	500ml bottled water	1000.00	200	water.jpg	2025-11-25 20:10:45.816358	\N
3	Notebook	A4 size notebook for students	3000.00	50	notebook.jpg	2025-11-25 20:10:45.816358	\N
4	Pen Set	Pack of 5 blue pens	2500.00	75	pens.jpg	2025-11-25 20:10:45.816358	\N
5	Energy Drink	250ml energy drink	3500.00	80	energy.jpg	2025-11-25 20:10:45.816358	\N
6	Bread	Fresh bread loaf	4000.00	30	bread.jpg	2025-11-25 20:10:45.816358	\N
\.


--
-- Data for Name: riders; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.riders (id, user_id, license_plate, bike_type, phone, location, is_available, rating, total_rides, created_at) FROM stdin;
1	3	Ufk279/9	Motorcycle	0765008037	Kamwokya	t	5.0	0	2025-11-25 21:42:05.25372
\.


--
-- Data for Name: room_layout; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.room_layout (id, user_id, layout_name, layout_json, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: rooms; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.rooms (id, hostel_id, room_number, room_type, capacity, price, availability, created_at) FROM stdin;
1	1	R101	Single	1	150000.00	t	2025-11-25 20:10:45.816358
2	1	R102	Double	2	250000.00	t	2025-11-25 20:10:45.816358
3	1	R103	Triple	3	350000.00	t	2025-11-25 20:10:45.816358
4	2	R201	Single	1	140000.00	t	2025-11-25 20:10:45.816358
5	2	R202	Double	2	240000.00	t	2025-11-25 20:10:45.816358
6	3	R301	Single	1	160000.00	t	2025-11-25 20:10:45.816358
8	4	R401	Double	2	260000.00	t	2025-11-25 20:10:45.816358
7	3	R302	Triple	3	360000.00	f	2025-11-25 20:10:45.816358
\.


--
-- Data for Name: transport; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.transport (id, user_id, pickup_location, destination, rider_name, cost, status, created_at, rider_id) FROM stdin;
1	2	Isbat main gate	Akamwesi	Jane Smith	12000.00	assigned	2025-11-25 20:36:54.639644	1
14	2	Isbat main gate	Valley courts hostel	\N	2000.00	assigned	2025-11-25 21:50:16.950687	1
13	2	Isbat main gate	Kamwokya	Mike Johnson	2500.00	assigned	2025-11-25 21:45:13.38209	1
12	2	Isbat main gate	Kamwokya	Jane Smith	2500.00	assigned	2025-11-25 21:39:47.35198	1
11	2	Isbat main gate	Kamwokya	Jane Smith	2000.00	assigned	2025-11-25 21:39:36.26193	1
10	2	Isbat main gate	Kajansi	Jane Smith	1750.00	assigned	2025-11-25 21:39:20.027784	1
8	2	Isbat main gate	Akamwesi	Jane Smith	250.00	assigned	2025-11-25 21:38:38.615249	1
9	2	Isbat main gate	Etower	Mike Johnson	2500.00	assigned	2025-11-25 21:39:01.14257	1
7	2	Isbat main gate	Valley courts hostel	Mike Johnson	500.00	assigned	2025-11-25 21:38:09.510353	1
6	2	Isbat main gate	Kamwokya	Mike Johnson	2500.00	assigned	2025-11-25 21:37:39.468845	1
5	2	Isbat main gate	Kamwokya	Mike Johnson	4500.00	assigned	2025-11-25 21:36:17.554475	1
4	2	Isbat main gate	Kamwokya	John Doe	6000.00	assigned	2025-11-25 21:34:42.528692	1
3	2	Isbat main gate	Valley courts hostel	Jane Smith	10000.00	assigned	2025-11-25 21:34:18.918387	1
2	2	Isbat university	Valley courts hostel	Mike Johnson	10000.00	assigned	2025-11-25 20:49:18.616359	1
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.users (id, name, email, password, role, phone, hostel_id, created_at) FROM stdin;
1	Admin User	admin@hostel.com	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	admin	\N	\N	2025-11-25 20:10:45.816358
2	ATULINDE CLINTON	clintonatulinde@gmail.com	$2y$10$mhG.pQ62xiy39mnlqgwahesoWbh.uBDeH2nR5BOjVZ8Hv3SMqVVuy	student	0765008037	\N	2025-11-25 20:35:08.073708
3	ATULINDE CLINTON	amazingclinton256@gmail.com	$2y$10$dqCUInrbgb1uX8iorsBXCOBUkXyIFPbJTAs7sfsh2DReBuLj173ga	boda_rider	0765008037	\N	2025-11-25 21:24:53.544766
\.


--
-- Name: bookings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.bookings_id_seq', 1, true);


--
-- Name: feedback_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.feedback_id_seq', 1, false);


--
-- Name: hostels_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.hostels_id_seq', 4, true);


--
-- Name: order_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.order_items_id_seq', 1, false);


--
-- Name: orders_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.orders_id_seq', 1, false);


--
-- Name: products_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.products_id_seq', 6, true);


--
-- Name: riders_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.riders_id_seq', 1, true);


--
-- Name: room_layout_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.room_layout_id_seq', 1, false);


--
-- Name: rooms_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.rooms_id_seq', 8, true);


--
-- Name: transport_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.transport_id_seq', 14, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.users_id_seq', 3, true);


--
-- Name: bookings bookings_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.bookings
    ADD CONSTRAINT bookings_pkey PRIMARY KEY (id);


--
-- Name: feedback feedback_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.feedback
    ADD CONSTRAINT feedback_pkey PRIMARY KEY (id);


--
-- Name: hostels hostels_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.hostels
    ADD CONSTRAINT hostels_pkey PRIMARY KEY (id);


--
-- Name: order_items order_items_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.order_items
    ADD CONSTRAINT order_items_pkey PRIMARY KEY (id);


--
-- Name: orders orders_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.orders
    ADD CONSTRAINT orders_pkey PRIMARY KEY (id);


--
-- Name: products products_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT products_pkey PRIMARY KEY (id);


--
-- Name: riders riders_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.riders
    ADD CONSTRAINT riders_pkey PRIMARY KEY (id);


--
-- Name: riders riders_user_id_key; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.riders
    ADD CONSTRAINT riders_user_id_key UNIQUE (user_id);


--
-- Name: room_layout room_layout_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.room_layout
    ADD CONSTRAINT room_layout_pkey PRIMARY KEY (id);


--
-- Name: rooms rooms_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.rooms
    ADD CONSTRAINT rooms_pkey PRIMARY KEY (id);


--
-- Name: transport transport_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.transport
    ADD CONSTRAINT transport_pkey PRIMARY KEY (id);


--
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: bookings bookings_room_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.bookings
    ADD CONSTRAINT bookings_room_id_fkey FOREIGN KEY (room_id) REFERENCES public.rooms(id) ON DELETE CASCADE;


--
-- Name: bookings bookings_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.bookings
    ADD CONSTRAINT bookings_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: feedback feedback_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.feedback
    ADD CONSTRAINT feedback_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: hostels hostels_owner_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.hostels
    ADD CONSTRAINT hostels_owner_id_fkey FOREIGN KEY (owner_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: order_items order_items_order_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.order_items
    ADD CONSTRAINT order_items_order_id_fkey FOREIGN KEY (order_id) REFERENCES public.orders(id) ON DELETE CASCADE;


--
-- Name: order_items order_items_product_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.order_items
    ADD CONSTRAINT order_items_product_id_fkey FOREIGN KEY (product_id) REFERENCES public.products(id) ON DELETE CASCADE;


--
-- Name: orders orders_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.orders
    ADD CONSTRAINT orders_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: products products_owner_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT products_owner_id_fkey FOREIGN KEY (owner_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: riders riders_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.riders
    ADD CONSTRAINT riders_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: room_layout room_layout_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.room_layout
    ADD CONSTRAINT room_layout_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: rooms rooms_hostel_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.rooms
    ADD CONSTRAINT rooms_hostel_id_fkey FOREIGN KEY (hostel_id) REFERENCES public.hostels(id) ON DELETE CASCADE;


--
-- Name: transport transport_rider_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.transport
    ADD CONSTRAINT transport_rider_id_fkey FOREIGN KEY (rider_id) REFERENCES public.riders(id) ON DELETE SET NULL;


--
-- Name: transport transport_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.transport
    ADD CONSTRAINT transport_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: DEFAULT PRIVILEGES FOR SEQUENCES; Type: DEFAULT ACL; Schema: public; Owner: cloud_admin
--

ALTER DEFAULT PRIVILEGES FOR ROLE cloud_admin IN SCHEMA public GRANT ALL ON SEQUENCES TO neon_superuser WITH GRANT OPTION;


--
-- Name: DEFAULT PRIVILEGES FOR TABLES; Type: DEFAULT ACL; Schema: public; Owner: cloud_admin
--

ALTER DEFAULT PRIVILEGES FOR ROLE cloud_admin IN SCHEMA public GRANT ALL ON TABLES TO neon_superuser WITH GRANT OPTION;


--
-- PostgreSQL database dump complete
--

\unrestrict Jo45P0qKSdRt7NyoH4vmg9kAJmaspgYeERp3RrnrHBMqbou9xIkqYbZ8UNMMJaz

