#ifndef STEAMPROFILESGUI_H
#define STEAMPROFILESGUI_H

#include <QMainWindow>
#include <qmessagebox.h>

namespace Ui
{
    class SteamProfilesGUI;
}

class SteamProfilesGUI: public QMainWindow
{
    Q_OBJECT

    public:
    explicit SteamProfilesGUI(QWidget *parent = 0);
    ~SteamProfilesGUI();

    private:
    Ui::SteamProfilesGUI *ui;

    private slots:
    void on_actionAbout_triggered();
    void on_actionExit_triggered();
};

#endif // STEAMPROFILESGUI_H
