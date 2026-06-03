GYM MANAGEMENT SYSTEM


flowchart TD

subgraph group_runtime["Runtime"]
  node_dockerfile["Dockerfile<br/>container build"]
end

subgraph group_public["Public"]
  node_index["Home<br/>entry page<br/>[index.php]"]
  node_login["Login<br/>auth page<br/>[login.php]"]
  node_register["Register<br/>auth page<br/>[register.php]"]
  node_register_member["Member Sign-up<br/>intake page"]
  node_logout["Logout<br/>session action<br/>[logout.php]"]
end

subgraph group_shared["Shared"]
  node_config["Config<br/>[config.php]"]
  node_includes["Includes<br/>shared UI"]
  node_assets["Assets"]
end

subgraph group_app["App"]
  node_dashboard["Dashboard<br/>ops overview<br/>[index.php]"]
  node_members["Members<br/>member module"]
  node_plans["Plans<br/>plan module"]
  node_payments["Payments<br/>billing module"]
  node_notifications["Notifications<br/>alert module<br/>[index.php]"]
  node_reports["Reports<br/>reporting module"]
  node_settings["Settings<br/>account module"]
end

subgraph group_data["Data"]
  node_schema[("Gym Schema<br/>sql schema<br/>[gym_saas.sql]")]
end

node_dockerfile -->|"runs app"| node_config
node_index -->|"loads"| node_config
node_login -->|"loads"| node_config
node_register -->|"loads"| node_config
node_register_member -->|"loads"| node_config
node_logout -->|"loads"| node_config
node_dashboard -->|"composes"| node_includes
node_members -->|"composes"| node_includes
node_plans -->|"composes"| node_includes
node_payments -->|"composes"| node_includes
node_notifications -->|"composes"| node_includes
node_reports -->|"composes"| node_includes
node_settings -->|"composes"| node_includes
node_includes -->|"uses"| node_config
node_includes -->|"queries"| node_schema
node_dashboard -->|"summarizes"| node_members
node_dashboard -->|"summarizes"| node_plans
node_dashboard -->|"summarizes"| node_payments
node_dashboard -->|"surfaces"| node_notifications
node_members -->|"reads/writes"| node_schema
node_plans -->|"reads/writes"| node_schema
node_payments -->|"reads/writes"| node_schema
node_notifications -->|"reads"| node_schema
node_reports -->|"reads"| node_schema
node_settings -->|"reads/writes"| node_schema
node_assets -->|"styles/ui"| node_dashboard
node_assets -->|"styles/ui"| node_members
node_assets -->|"styles/ui"| node_plans
node_assets -->|"styles/ui"| node_payments

click node_dockerfile "https://github.com/kunalkharga/gymmanagementsystem/tree/master/Dockerfile"
click node_index "https://github.com/kunalkharga/gymmanagementsystem/blob/master/index.php"
click node_login "https://github.com/kunalkharga/gymmanagementsystem/blob/master/login.php"
click node_register "https://github.com/kunalkharga/gymmanagementsystem/blob/master/register.php"
click node_register_member "https://github.com/kunalkharga/gymmanagementsystem/blob/master/register-member.php"
click node_logout "https://github.com/kunalkharga/gymmanagementsystem/blob/master/logout.php"
click node_config "https://github.com/kunalkharga/gymmanagementsystem/blob/master/config.php"
click node_includes "https://github.com/kunalkharga/gymmanagementsystem/tree/master/includes"
click node_assets "https://github.com/kunalkharga/gymmanagementsystem/tree/master/assets"
click node_dashboard "https://github.com/kunalkharga/gymmanagementsystem/blob/master/dashboard/index.php"
click node_members "https://github.com/kunalkharga/gymmanagementsystem/tree/master/members"
click node_plans "https://github.com/kunalkharga/gymmanagementsystem/tree/master/plans"
click node_payments "https://github.com/kunalkharga/gymmanagementsystem/tree/master/payments"
click node_notifications "https://github.com/kunalkharga/gymmanagementsystem/blob/master/notifications/index.php"
click node_reports "https://github.com/kunalkharga/gymmanagementsystem/tree/master/reports"
click node_settings "https://github.com/kunalkharga/gymmanagementsystem/tree/master/settings"
click node_schema "https://github.com/kunalkharga/gymmanagementsystem/blob/master/database/gym_saas.sql"

classDef toneNeutral fill:#f8fafc,stroke:#334155,stroke-width:1.5px,color:#0f172a
classDef toneBlue fill:#dbeafe,stroke:#2563eb,stroke-width:1.5px,color:#172554
classDef toneAmber fill:#fef3c7,stroke:#d97706,stroke-width:1.5px,color:#78350f
classDef toneMint fill:#dcfce7,stroke:#16a34a,stroke-width:1.5px,color:#14532d
classDef toneRose fill:#ffe4e6,stroke:#e11d48,stroke-width:1.5px,color:#881337
classDef toneIndigo fill:#e0e7ff,stroke:#4f46e5,stroke-width:1.5px,color:#312e81
classDef toneTeal fill:#ccfbf1,stroke:#0f766e,stroke-width:1.5px,color:#134e4a
class node_dockerfile toneBlue
class node_index,node_login,node_register,node_register_member,node_logout toneAmber
class node_config,node_includes,node_assets toneMint
class node_dashboard,node_members,node_plans,node_payments,node_notifications,node_reports,node_settings toneRose
class node_schema toneIndigo
