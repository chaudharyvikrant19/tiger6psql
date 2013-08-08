CREATE TABLE vtiger_module_dashboard_widgets
(
  id serial NOT NULL,
  linkid bigint,
  userid bigint,
  filterid bigint,
  title character varying(100),
  data character varying(500) DEFAULT '[]'::character varying,
  CONSTRAINT vtiger_module_dashboard_widgets_pkey PRIMARY KEY (id)
);