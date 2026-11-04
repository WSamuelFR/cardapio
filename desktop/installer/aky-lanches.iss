; Script de Instalação Inno Setup para AKY Lanches Desktop
; Compatível com Inno Setup 6

#define MyAppName "AKY Lanches"
#define MyAppVersion "1.0.0"
#define MyAppPublisher "AKY Lanches"
#define MyAppURL "https://akylanches.com.br"
#define MyAppExeName "AKY Lanches.exe"
#define MyAppIconName "..\assets\icon.ico"

[Setup]
AppId={{C8E19B14-99D2-48F1-A3C8-58B2E4929D12}
AppName={#MyAppName}
AppVersion={#MyAppVersion}
AppPublisher={#MyAppPublisher}
AppPublisherURL={#MyAppURL}
AppSupportURL={#MyAppURL}
AppUpdatesURL={#MyAppURL}
DefaultDirName={autopf}\{#MyAppName}
DefaultGroupName={#MyAppName}
AllowNoIcons=yes
OutputDir=..\dist-app\installer
OutputBaseFilename=AKY-Lanches-Instalador-v{#MyAppVersion}
SetupIconFile={#MyAppIconName}
Compression=lzma2/ultra64
SolidCompression=yes
WizardStyle=modern
ArchitecturesInstallIn64BitMode=x64
PrivilegesRequired=admin

[Languages]
Name: "brazilianportuguese"; MessagesFile: "compiler:Languages\BrazilianPortuguese.isl"

[Tasks]
Name: "desktopicon"; Description: "{cm:CreateDesktopIcon}"; GroupDescription: "{cm:AdditionalIcons}"; Flags: unchecked

[Files]
; Todos os arquivos da aplicação empacotada pelo electron-builder
Source: "..\dist-app\win-unpacked\*"; DestDir: "{app}"; Flags: ignoreversion recursesubdirs createallsubdirs

[Dirs]
; Garante permissões de escrita para pastas de dados locais, sessões e SQLite
Name: "{app}\resources\laravel\database\backup"; Permissions: users-full
Name: "{app}\resources\laravel\storage"; Permissions: users-full
Name: "{app}\resources\laravel\storage\framework"; Permissions: users-full
Name: "{app}\resources\laravel\storage\logs"; Permissions: users-full
Name: "{app}\resources\laravel\bootstrap\cache"; Permissions: users-full

[Icons]
Name: "{group}\{#MyAppName}"; Filename: "{app}\{#MyAppExeName}"; IconFilename: "{app}\{#MyAppExeName}"
Name: "{group}\{cm:UninstallProgram,{#MyAppName}}"; Filename: "{uninstallexe}"
Name: "{autodesktop}\{#MyAppName}"; Filename: "{app}\{#MyAppExeName}"; Tasks: desktopicon; IconFilename: "{app}\{#MyAppExeName}"

[Run]
Filename: "{app}\{#MyAppExeName}"; Description: "{cm:LaunchProgram,{#StringChange(MyAppName, '&', '&&')}}"; Flags: nowait postinstall skipifsilent

[UninstallDelete]
Type: filesandordirs; Name: "{app}\resources\laravel\storage\framework\cache\*"
Type: filesandordirs; Name: "{app}\resources\laravel\storage\framework\sessions\*"
Type: filesandordirs; Name: "{app}\resources\laravel\storage\framework\views\*"

