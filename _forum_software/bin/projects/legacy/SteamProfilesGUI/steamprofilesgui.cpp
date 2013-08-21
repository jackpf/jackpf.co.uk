#include "steamprofilesgui.h"
#include "ui_steamprofilesgui.h"

SteamProfilesGUI::SteamProfilesGUI(QWidget *parent):
    QMainWindow(parent),
    ui(new Ui::SteamProfilesGUI)
{
    ui->setupUi(this);

    QLabel *Label = new QLabel(this);
    Label->setGeometry(30, 80, 50, 25);
    Label->setText("Profiles...");
}

SteamProfilesGUI::~SteamProfilesGUI()
{
    delete ui;
}

void SteamProfilesGUI::on_actionAbout_triggered()
{
    QMessageBox::about(this, "About", "Steam Profiles.\nCreated by Jackpf.");
}

void SteamProfilesGUI::on_actionExit_triggered()
{
    QMessageBox::about(this, "Exit", "Now exiting...");
    QCoreApplication::exit();
}
