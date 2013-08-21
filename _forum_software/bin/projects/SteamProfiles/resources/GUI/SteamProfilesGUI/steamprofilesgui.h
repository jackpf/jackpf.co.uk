#ifndef STEAMPROFILESGUI_H
#define STEAMPROFILESGUI_H

#include <QMainWindow>
#include <QMessageBox>
#include <QRadioButton>
#include <QPushButton>
#include <QSystemTrayIcon>
#include <QDialog>
#include <windows.h>
#include <iostream>
#include <stdio.h>

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
    void on_ProfileSubmit();
    void on_actionAbout_triggered();
    void on_actionExit_triggered();
    bool event(QEvent *event);
};

#endif // STEAMPROFILESGUI_H
