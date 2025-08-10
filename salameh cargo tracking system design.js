{
    "meta": {
      "project": "Salameh Cargo — Custom Tracking Platform",
      "owner": "salamehcargo.com",
      "host": "Hostinger",
      "stack": {
        "backend": "PHP 8+ (no frameworks)",
        "frontend": "HTML5, CSS3, Vanilla JavaScript",
        "db": "MySQL (XAMPP dev)",
        "libs_allowed": ["php-curl", "DOMDocument/SimpleXML", "optional: PHPSpreadsheet for .xlsx parsing", "optional: Simple HTML DOM (single-file include)"],
        "libs_disallowed": ["Laravel, Symfony, WordPress, React, Vue, jQuery or any frontend/backend framework"]
      },
      "principles": [
        "Futuristic, responsive UI",
        "Security-first (prepared statements, session hardening)",
        "No vendor lock-in; simple file-based PHP pages",
        "Automation by default, manual override available",
        "Deterministic rebuild from this prompt"
      ],
      "version": "2025-08-10"
    },
    "functional_scope": {
      "public_site": {
        "pages": [
          {
            "path": "/index.php",
            "name": "Home",
            "hero": "Central tracking search",
            "cta": ["Track Now"]
          },
          {
            "path": "/track.php",
            "name": "Track Your Shipment",
            "search_fields_supported": [
              "tracking_number",
              "container_number",
              "bill_of_lading",
              "air_waybill",
              "phone",
              "full_name",
              "shipping_code"
            ],
            "search_strategy": {
              "policy": "db_first",
              "policy_overrides": ["querystring live=1 to force live scrape"],
              "result_badging": ["source: database|live", "last_updated_ts"]
            }
          },
          {
            "path": "/about.php",
            "name": "About Us"
          },
          {
            "path": "/contact.php",
            "name": "Contact",
            "whatsapp_click_to_chat": true,
            "whatsapp_number_env": "WHATSAPP_NUMBER"
          },
          {
            "path": "/login.php",
            "name": "OTP Login (WhatsApp)",
            "flow": ["enter_phone", "send_otp", "verify_otp", "session_start", "redirect:/dashboard.php"]
          },
          {
            "path": "/dashboard.php",
            "name": "User Dashboard",
            "auth": "required",
            "data": "shipments owned by user (phone/ID link) with table + filters"
          }
        ],
        "guest_policy": {
          "can_track_any_identifier": true
        },
        "logged_in_policy": {
          "default_view": "only user-owned shipments",
          "can_search_others": true
        }
      },
      "admin_panel": {
        "base": "/admin/",
        "auth": "username/password (hashed)",
        "pages": [
          {
            "path": "/admin/index.php",
            "name": "Admin Dashboard",
            "widgets": ["counts_by_status", "recent_activity", "manual_refresh_all"]
          },
          {
            "path": "/admin/add_user.php",
            "name": "Add User (manual only)",
            "inputs": ["full_name", "email", "phone", "shipping_code", "address", "country", "id_number"]
          },
          {
            "path": "/admin/upload_shipments.php",
            "name": "Upload Shipments (Excel)",
            "accepts": [".xlsx", ".csv"],
            "mapping": {
              "detect_user": ["by filename prefix matching users.shipping_code", "or dropdown select"],
              "totals": ["cbm", "cartons", "weight", "gross_weight", "total_amount"],
              "identifiers_optional": ["container_number", "bill_of_lading"]
            },
            "post_import_status_default": "En Route"
          },
          {
            "path": "/admin/shipments.php",
            "name": "Manage Shipments",
            "features": ["list + filter + sort", "edit/update status", "view details + scrape history", "delete (role-gated)", "on-demand refresh per shipment"]
          },
          {
            "path": "/admin/automation.php",
            "name": "Automation Controls",
            "toggles": ["enable_cron", "rate_limit", "source_order"],
            "manual_actions": ["refresh_all", "rebuild_indexes"]
          }
        ]
      }
    },
    "automation_tracking": {
      "scrape_per_shipment": true,
      "sources": [
        "WTO Cargo",
        "CMA CGM",
        "MSC",
        "ONE (Ocean Network Express)",
        "Maersk",
        "Evergreen (EMC)",
        "track-trace.com",
        "searates.com",
        "shipsgo.com",
        "gocomet.com",
        "findteu.com",
        "MarineTraffic",
        "VesselFinder",
        "BCTC (Beirut)",
        "Port of Beirut"
      ],
      "identifier_detection": {
        "container_regex": "^[A-Z]{4}\\d{7}$",
        "bl_regex": "^[A-Z0-9\\-]{6,}$",
        "air_waybill_regex": "^[0-9]{3}-?[0-9]{8}$"
      },
      "scheduler": {
        "type": "cron/task-scheduler",
        "frequency": "daily",
        "script": "/cron/update_shipments.php",
        "selection": "all shipments where status != 'Delivered'"
      },
      "on_demand": {
        "admin_button": true,
        "user_trigger_on_view": {
          "enabled": true,
          "debounce_minutes": 60
        }
      },
      "update_logic": {
        "normalize_map_examples": {
          "Loaded on vessel": "In Transit",
          "Discharged at destination": "Arrived at Port",
          "Gate out full": "Delivered"
        },
        "priority": ["carrier_official", "port_terminal", "aggregators"],
        "fallback": "use last known DB status; show staleness badge"
      },
      "telemetry": {
        "log_errors": true,
        "store_raw_html_snippet": false
      },
      "legal_note": "Prefer official APIs when available; scraping may break or be rate-limited."
    },
    "auth": {
      "users": {
        "login": "passwordless OTP via WhatsApp",
        "provider": "configurable (Twilio Verify or WhatsApp Cloud API). If none configured, use DEV_MODE that prints OTP to server logs for testing.",
        "otp_length": 6,
        "otp_ttl_minutes": 10,
        "rate_limit_per_phone_per_hour": 5
      },
      "admins": {
        "login": "username/password",
        "hash": "password_hash()/password_verify()",
        "roles": ["superadmin", "manager", "clerk"]
      },
      "sessions": {
        "http_only": true,
        "regenerate_on_login": true,
        "idle_timeout_minutes": 30
      }
    },
    "database": {
      "name": "salameh_cargo",
      "tables": {
        "users": {
          "pk": "user_id INT AI",
          "columns": {
            "full_name": "VARCHAR(255) NOT NULL",
            "email": "VARCHAR(255) NULL",
            "phone": "VARCHAR(50) NOT NULL UNIQUE",
            "shipping_code": "VARCHAR(100) UNIQUE NULL",
            "address": "VARCHAR(255) NULL",
            "country": "VARCHAR(100) NULL",
            "id_number": "VARCHAR(100) NULL",
            "created_at": "DATETIME DEFAULT CURRENT_TIMESTAMP"
          }
        },
        "shipments": {
          "pk": "shipment_id INT AI",
          "fk": {"user_id": "users.user_id"},
          "columns": {
            "tracking_number": "VARCHAR(100) UNIQUE",
            "product_description": "TEXT NULL",
            "cbm": "DECIMAL(10,2) DEFAULT 0",
            "cartons": "INT DEFAULT 0",
            "weight": "DECIMAL(10,2) DEFAULT 0",
            "gross_weight": "DECIMAL(10,2) DEFAULT 0",
            "total_amount": "DECIMAL(10,2) DEFAULT 0",
            "status": "VARCHAR(50) DEFAULT 'En Route'",
            "origin": "VARCHAR(100) NULL",
            "destination": "VARCHAR(100) NULL",
            "pickup_date": "DATETIME NULL",
            "delivery_date": "DATETIME NULL",
            "created_at": "DATETIME DEFAULT CURRENT_TIMESTAMP"
          },
          "indexes": ["user_id", "tracking_number", "status"]
        },
        "shipment_scrapes": {
          "pk": "scrape_id INT AI",
          "fk": {"shipment_id": "shipments.shipment_id ON DELETE CASCADE"},
          "columns": {
            "source_site": "VARCHAR(50)",
            "status": "VARCHAR(100)",
            "status_raw": "TEXT",
            "scrape_time": "DATETIME DEFAULT CURRENT_TIMESTAMP"
          },
          "indexes": ["shipment_id", "source_site", "scrape_time"]
        },
        "admins": {
          "pk": "admin_id INT AI",
          "columns": {
            "username": "VARCHAR(100) UNIQUE",
            "password_hash": "VARCHAR(255)",
            "role": "VARCHAR(50) DEFAULT 'manager'",
            "created_at": "DATETIME DEFAULT CURRENT_TIMESTAMP"
          }
        },
        "logs": {
          "pk": "log_id INT AI",
          "columns": {
            "action_type": "VARCHAR(50)",
            "actor_id": "INT COMMENT 'positive=user_id, negative=admin_id'",
            "related_shipment_id": "INT NULL",
            "details": "TEXT NULL",
            "timestamp": "DATETIME DEFAULT CURRENT_TIMESTAMP"
          },
          "indexes": ["actor_id", "related_shipment_id", "timestamp"]
        }
      }
    },
    "excel_import": {
      "parser": "prefer PHPSpreadsheet; allow CSV fallback",
      "assumptions": [
        "one file = one shipment batch",
        "filenames may include shipping_code (e.g., ZAHER05YW25.xlsx)"
      ],
      "mapping_rules": {
        "cbm": "sum or TOTAL CBM cell",
        "cartons": "sum or TOTAL CARTONS cell",
        "weight": "sum of NW if present",
        "gross_weight": "sum or TOTAL GW cell",
        "total_amount": "sum or TOTAL AMOUNT cell",
        "tracking_number": "from file content or filename (fallback generate unique code)"
      },
      "ui_flow": ["choose file", "select/confirm user", "preview totals", "commit insert"]
    },
    "ui_ux": {
      "theme": "dark base with neon accents; clean typography; mobile-first",
      "components": ["sticky header", "accessible forms", "status badges", "table with responsive stacking"],
      "contact_whatsapp_cta": true
    },
    "security": {
      "sql": "PDO prepared statements only",
      "xss": "htmlspecialchars on output",
      "csrf": "token on admin mutating forms",
      "uploads": "verify mime/ext; store outside web root; delete after parse",
      "rate_limits": {"otp": "5/hour/phone", "live_scrape": "per IP debounce"}
    },
    "file_structure": {
      "public": ["/index.php", "/track.php", "/about.php", "/contact.php", "/login.php", "/dashboard.php"],
      "admin": ["/admin/index.php", "/admin/login.php", "/admin/add_user.php", "/admin/upload_shipments.php", "/admin/shipments.php", "/admin/automation.php"],
      "includes": ["/includes/config.php", "/includes/db.php", "/includes/header.php", "/includes/footer.php", "/includes/auth.php", "/includes/scrapers/*.php"],
      "cron": ["/cron/update_shipments.php"],
      "assets": ["/assets/css/styles.css", "/assets/js/app.js"]
    },
    "acceptance_criteria": [
      "Guest can query by any supported identifier and see results with source badge.",
      "Logged-in user sees only owned shipments by default; can search others.",
      "Admin can add user manually, upload shipments from Excel, edit statuses, and trigger refresh.",
      "Cron updates in-transit shipments daily and writes scrape history.",
      "DB schema matches this JSON and installs via a single SQL script.",
      "All queries use prepared statements; sessions hardened; passwords hashed."
    ],
    "test_matrix": {
      "happy_paths": ["guest exact tracking", "user OTP login success", "excel import valid", "admin edit status", "cron updates status"],
      "edge_cases": ["multiple matches by phone/name", "live scrape timeout -> fallback DB", "OTP expired", "duplicate tracking_number", "Excel headers missing"],
      "nonfunctional": ["mobile viewport <375px", "10k shipments pagination", "scraper error logging"]
    },
    "deliverables": [
      "Complete PHP pages per file_structure",
      "SQL create script + seed admin (username=admin, password=change_me)",
      "Scraper stubs for each source with graceful failure",
      "Cron/task scheduler instructions for Hostinger",
      "README with .env-style config keys"
    ]
  }
{
  "meta": {
    "project": "Salameh Cargo — Custom Tracking Platform",
    "owner": "salamehcargo.com",
    "host": "Hostinger",
    "stack": {
      "backend": "PHP 8+ (no frameworks)",
      "frontend": "HTML5, CSS3, Vanilla JavaScript",
      "db": "MySQL (XAMPP dev)",
      "libs_allowed": ["php-curl", "DOMDocument/SimpleXML", "optional: PHPSpreadsheet for .xlsx parsing", "optional: Simple HTML DOM (single-file include)"],
      "libs_disallowed": ["Laravel, Symfony, WordPress, React, Vue, jQuery or any frontend/backend framework"]
    },
    "principles": [
      "Futuristic, responsive UI",
      "Security-first (prepared statements, session hardening)",
      "No vendor lock-in; simple file-based PHP pages",
      "Automation by default, manual override available",
      "Deterministic rebuild from this prompt"
    ],
    "version": "2025-08-10"
  },
  "functional_scope": {
    "public_site": {
      "pages": [
        {
          "path": "/index.php",
          "name": "Home",
          "hero": "Central tracking search",
          "cta": ["Track Now"]
        },
        {
          "path": "/track.php",
          "name": "Track Your Shipment",
          "search_fields_supported": [
            "tracking_number",
            "container_number",
            "bill_of_lading",
            "air_waybill",
            "phone",
            "full_name",
            "shipping_code"
          ],
          "search_strategy": {
            "policy": "db_first",
            "policy_overrides": ["querystring live=1 to force live scrape"],
            "result_badging": ["source: database|live", "last_updated_ts"]
          }
        },
        {
          "path": "/about.php",
          "name": "About Us"
        },
        {
          "path": "/contact.php",
          "name": "Contact",
          "whatsapp_click_to_chat": true,
          "whatsapp_number_env": "WHATSAPP_NUMBER"
        },
        {
          "path": "/login.php",
          "name": "OTP Login (WhatsApp)",
          "flow": ["enter_phone", "send_otp", "verify_otp", "session_start", "redirect:/dashboard.php"]
        },
        {
          "path": "/dashboard.php",
          "name": "User Dashboard",
          "auth": "required",
          "data": "shipments owned by user (phone/ID link) with table + filters"
        }
      ],
      "guest_policy": {
        "can_track_any_identifier": true
      },
      "logged_in_policy": {
        "default_view": "only user-owned shipments",
        "can_search_others": true
      }
    },
    "admin_panel": {
      "base": "/admin/",
      "auth": "username/password (hashed)",
      "pages": [
        {
          "path": "/admin/index.php",
          "name": "Admin Dashboard",
          "widgets": ["counts_by_status", "recent_activity", "manual_refresh_all"]
        },
        {
          "path": "/admin/add_user.php",
          "name": "Add User (manual only)",
          "inputs": ["full_name", "email", "phone", "shipping_code", "address", "country", "id_number"]
        },
        {
          "path": "/admin/upload_shipments.php",
          "name": "Upload Shipments (Excel)",
          "accepts": [".xlsx", ".csv"],
          "mapping": {
            "detect_user": ["by filename prefix matching users.shipping_code", "or dropdown select"],
            "totals": ["cbm", "cartons", "weight", "gross_weight", "total_amount"],
            "identifiers_optional": ["container_number", "bill_of_lading"]
          },
          "post_import_status_default": "En Route"
        },
        {
          "path": "/admin/shipments.php",
          "name": "Manage Shipments",
          "features": ["list + filter + sort", "edit/update status", "view details + scrape history", "delete (role-gated)", "on-demand refresh per shipment"]
        },
        {
          "path": "/admin/automation.php",
          "name": "Automation Controls",
          "toggles": ["enable_cron", "rate_limit", "source_order"],
          "manual_actions": ["refresh_all", "rebuild_indexes"]
        }
      ]
    }
  },
  "automation_tracking": {
    "scrape_per_shipment": true,
    "sources": [
      "WTO Cargo",
      "CMA CGM",
      "MSC",
      "ONE (Ocean Network Express)",
      "Maersk",
      "Evergreen (EMC)",
      "track-trace.com",
      "searates.com",
      "shipsgo.com",
      "gocomet.com",
      "findteu.com",
      "MarineTraffic",
      "VesselFinder",
      "BCTC (Beirut)",
      "Port of Beirut"
    ],
    "identifier_detection": {
      "container_regex": "^[A-Z]{4}\\d{7}$",
      "bl_regex": "^[A-Z0-9\\-]{6,}$",
      "air_waybill_regex": "^[0-9]{3}-?[0-9]{8}$"
    },
    "scheduler": {
      "type": "cron/task-scheduler",
      "frequency": "daily",
      "script": "/cron/update_shipments.php",
      "selection": "all shipments where status != 'Delivered'"
    },
    "on_demand": {
      "admin_button": true,
      "user_trigger_on_view": {
        "enabled": true,
        "debounce_minutes": 60
      }
    },
    "update_logic": {
      "normalize_map_examples": {
        "Loaded on vessel": "In Transit",
        "Discharged at destination": "Arrived at Port",
        "Gate out full": "Delivered"
      },
      "priority": ["carrier_official", "port_terminal", "aggregators"],
      "fallback": "use last known DB status; show staleness badge"
    },
    "telemetry": {
      "log_errors": true,
      "store_raw_html_snippet": false
    },
    "legal_note": "Prefer official APIs when available; scraping may break or be rate-limited."
  },
  "auth": {
    "users": {
      "login": "passwordless OTP via WhatsApp",
      "provider": "configurable (Twilio Verify or WhatsApp Cloud API). If none configured, use DEV_MODE that prints OTP to server logs for testing.",
      "otp_length": 6,
      "otp_ttl_minutes": 10,
      "rate_limit_per_phone_per_hour": 5
    },
    "admins": {
      "login": "username/password",
      "hash": "password_hash()/password_verify()",
      "roles": ["superadmin", "manager", "clerk"]
    },
    "sessions": {
      "http_only": true,
      "regenerate_on_login": true,
      "idle_timeout_minutes": 30
    }
  },
  "database": {
    "name": "salameh_cargo",
    "tables": {
      "users": {
        "pk": "user_id INT AI",
        "columns": {
          "full_name": "VARCHAR(255) NOT NULL",
          "email": "VARCHAR(255) NULL",
          "phone": "VARCHAR(50) NOT NULL UNIQUE",
          "shipping_code": "VARCHAR(100) UNIQUE NULL",
          "address": "VARCHAR(255) NULL",
          "country": "VARCHAR(100) NULL",
          "id_number": "VARCHAR(100) NULL",
          "created_at": "DATETIME DEFAULT CURRENT_TIMESTAMP"
        }
      },
      "shipments": {
        "pk": "shipment_id INT AI",
        "fk": {"user_id": "users.user_id"},
        "columns": {
          "tracking_number": "VARCHAR(100) UNIQUE",
          "product_description": "TEXT NULL",
          "cbm": "DECIMAL(10,2) DEFAULT 0",
          "cartons": "INT DEFAULT 0",
          "weight": "DECIMAL(10,2) DEFAULT 0",
          "gross_weight": "DECIMAL(10,2) DEFAULT 0",
          "total_amount": "DECIMAL(10,2) DEFAULT 0",
          "status": "VARCHAR(50) DEFAULT 'En Route'",
          "origin": "VARCHAR(100) NULL",
          "destination": "VARCHAR(100) NULL",
          "pickup_date": "DATETIME NULL",
          "delivery_date": "DATETIME NULL",
          "created_at": "DATETIME DEFAULT CURRENT_TIMESTAMP"
        },
        "indexes": ["user_id", "tracking_number", "status"]
      },
      "shipment_scrapes": {
        "pk": "scrape_id INT AI",
        "fk": {"shipment_id": "shipments.shipment_id ON DELETE CASCADE"},
        "columns": {
          "source_site": "VARCHAR(50)",
          "status": "VARCHAR(100)",
          "status_raw": "TEXT",
          "scrape_time": "DATETIME DEFAULT CURRENT_TIMESTAMP"
        },
        "indexes": ["shipment_id", "source_site", "scrape_time"]
      },
      "admins": {
        "pk": "admin_id INT AI",
        "columns": {
          "username": "VARCHAR(100) UNIQUE",
          "password_hash": "VARCHAR(255)",
          "role": "VARCHAR(50) DEFAULT 'manager'",
          "created_at": "DATETIME DEFAULT CURRENT_TIMESTAMP"
        }
      },
      "logs": {
        "pk": "log_id INT AI",
        "columns": {
          "action_type": "VARCHAR(50)",
          "actor_id": "INT COMMENT 'positive=user_id, negative=admin_id'",
          "related_shipment_id": "INT NULL",
          "details": "TEXT NULL",
          "timestamp": "DATETIME DEFAULT CURRENT_TIMESTAMP"
        },
        "indexes": ["actor_id", "related_shipment_id", "timestamp"]
      }
    }
  },
  "excel_import": {
    "parser": "prefer PHPSpreadsheet; allow CSV fallback",
    "assumptions": [
      "one file = one shipment batch",
      "filenames may include shipping_code (e.g., ZAHER05YW25.xlsx)"
    ],
    "mapping_rules": {
      "cbm": "sum or TOTAL CBM cell",
      "cartons": "sum or TOTAL CARTONS cell",
      "weight": "sum of NW if present",
      "gross_weight": "sum or TOTAL GW cell",
      "total_amount": "sum or TOTAL AMOUNT cell",
      "tracking_number": "from file content or filename (fallback generate unique code)"
    },
    "ui_flow": ["choose file", "select/confirm user", "preview totals", "commit insert"]
  },
  "ui_ux": {
    "theme": "dark base with neon accents; clean typography; mobile-first",
    "components": ["sticky header", "accessible forms", "status badges", "table with responsive stacking"],
    "contact_whatsapp_cta": true
  },
  "security": {
    "sql": "PDO prepared statements only",
    "xss": "htmlspecialchars on output",
    "csrf": "token on admin mutating forms",
    "uploads": "verify mime/ext; store outside web root; delete after parse",
    "rate_limits": {"otp": "5/hour/phone", "live_scrape": "per IP debounce"}
  },
  "file_structure": {
    "public": ["/index.php", "/track.php", "/about.php", "/contact.php", "/login.php", "/dashboard.php"],
    "admin": ["/admin/index.php", "/admin/login.php", "/admin/add_user.php", "/admin/upload_shipments.php", "/admin/shipments.php", "/admin/automation.php"],
    "includes": ["/includes/config.php", "/includes/db.php", "/includes/header.php", "/includes/footer.php", "/includes/auth.php", "/includes/scrapers/*.php"],
    "cron": ["/cron/update_shipments.php"],
    "assets": ["/assets/css/styles.css", "/assets/js/app.js"]
  },
  "acceptance_criteria": [
    "Guest can query by any supported identifier and see results with source badge.",
    "Logged-in user sees only owned shipments by default; can search others.",
    "Admin can add user manually, upload shipments from Excel, edit statuses, and trigger refresh.",
    "Cron updates in-transit shipments daily and writes scrape history.",
    "DB schema matches this JSON and installs via a single SQL script.",
    "All queries use prepared statements; sessions hardened; passwords hashed."
  ],
  "test_matrix": {
    "happy_paths": ["guest exact tracking", "user OTP login success", "excel import valid", "admin edit status", "cron updates status"],
    "edge_cases": ["multiple matches by phone/name", "live scrape timeout -> fallback DB", "OTP expired", "duplicate tracking_number", "Excel headers missing"],
    "nonfunctional": ["mobile viewport <375px", "10k shipments pagination", "scraper error logging"]
  },
  "deliverables": [
    "Complete PHP pages per file_structure",
    "SQL create script + seed admin (username=admin, password=change_me)",
    "Scraper stubs for each source with graceful failure",
    "Cron/task scheduler instructions for Hostinger",
    "README with .env-style config keys"
  ]
}
  