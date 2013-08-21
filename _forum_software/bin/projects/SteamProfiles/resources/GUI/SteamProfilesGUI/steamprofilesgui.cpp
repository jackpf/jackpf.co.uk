#include "steamprofilesgui.h"
#include "ui_steamprofilesgui.h"

SteamProfilesGUI::SteamProfilesGUI(QWidget *parent):
    QMainWindow(parent),
    ui(new Ui::SteamProfilesGUI)
{
    ui->setupUi(this);
    QIcon Icon("..\\..\\resources\\steamprofiles.ico");
    setWindowIcon(Icon);

    //QLabel *Label = new QLabel(this);
    //Label->setGeometry(30, 80, 100, 25);
    //Label->setText("Profiles...");

    HINSTANCE SteamGUILib = LoadLibrary(L"..\\..\\resources\\SteamGUI.dll");
    if(SteamGUILib)
    {
        typedef int (*GetProfiles)(char[64][128]);
        GetProfiles _GetProfiles;
        _GetProfiles = (GetProfiles) GetProcAddress(SteamGUILib, "GetProfiles");

        typedef void (*RunSteamProfile)(int);
        RunSteamProfile _RunSteamProfile;
        _RunSteamProfile = (RunSteamProfile) GetProcAddress(SteamGUILib, "RunSteamProfile");

        char Profiles[64][128];
        int NumProfiles = _GetProfiles(Profiles);

        if(NumProfiles > 0)
        {
            QLabel *ProfileLabel[NumProfiles];
            QButtonGroup *ProfileRadioButtonGroup = new QButtonGroup(this);
            QRadioButton *ProfileRadioButton[NumProfiles];
            for(int i = 1; i <= NumProfiles; i++)
            {
                ProfileRadioButton[i] = new QRadioButton(this);
                ProfileRadioButton[i]->setGeometry(30, 80 + (20 * (i - 1)), 25, 25);
                if(i == 1)
                    ProfileRadioButton[i]->setChecked(true);
                ProfileRadioButtonGroup->addButton(ProfileRadioButton[i], i);

                ProfileLabel[i] = new QLabel(this);
                ProfileLabel[i]->setGeometry(45, 80 + (20 * (i - 1)), 1000, 25);
                char ProfileDisplay[256];
                sprintf(ProfileDisplay, "%i) %s", i, Profiles[i]);
                ProfileLabel[i]->setText(ProfileDisplay);
                ProfileLabel[i]->setBuddy(ProfileRadioButton[i]);

                if(i == NumProfiles)
                {
                    QPushButton *ProfileSubmit = new QPushButton(this);
                    ProfileSubmit->setGeometry(40, 80 + (20 * (i - 1)) + 20, 50, 25);
                    ProfileSubmit->setText("Submit");
                    connect(ProfileSubmit, SIGNAL(clicked()), this, SLOT(on_ProfileSubmit()));
                }
            }
        }
        else
        {
            QLabel *Label = new QLabel(this);
            Label->setGeometry(30, 80, 1000, 25);
            Label->setText("No Profiles loaded.");
        }
    }
    else
    {
        QMessageBox::about(this, "SteamGUI.dll", "Error loading SteamGUI.dll.");
        QLabel *Label = new QLabel(this);
        Label->setGeometry(30, 80, 1000, 25);
        Label->setText("No Profiles loaded.");
    }

    QMenu *SystemTrayMenu = new QMenu(this);
    QAction *SystemTrayMenu_Exit = new QAction(tr("Exit"), this);
    connect(SystemTrayMenu_Exit, SIGNAL(triggered()), this, SLOT(on_actionExit_triggered()));
    SystemTrayMenu->addAction(SystemTrayMenu_Exit);

    QSystemTrayIcon *SystemTray = new QSystemTrayIcon(this);
    SystemTray->setContextMenu(SystemTrayMenu);
    SystemTray->setIcon(Icon);
    SystemTray->show();
}

SteamProfilesGUI::~SteamProfilesGUI()
{
    delete ui;
}

void SteamProfilesGUI::on_ProfileSubmit()
{
    QMessageBox::about(this, "Submitted", "TODO...");
}

void SteamProfilesGUI::on_actionAbout_triggered()
{
    QMessageBox::about(this, "About", "Steam Profiles.\nCreated by Jackpf.");
}

void SteamProfilesGUI::on_actionExit_triggered()
{
    //QMessageBox::about(this, "Exit", "Now exiting...");
    QCoreApplication::exit();
}

bool SteamProfilesGUI::event(QEvent *event)
{
    if(event->type() == QEvent::WindowStateChange && SteamProfilesGUI::windowState() & Qt::WindowMinimized)
    {
        //hide();
    }
    //...
    return true;
}
