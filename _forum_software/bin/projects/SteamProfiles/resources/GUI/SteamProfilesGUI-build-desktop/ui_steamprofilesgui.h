/********************************************************************************
** Form generated from reading UI file 'steamprofilesgui.ui'
**
** Created: Wed 19. Jan 16:52:07 2011
**      by: Qt User Interface Compiler version 4.7.0
**
** WARNING! All changes made in this file will be lost when recompiling UI file!
********************************************************************************/

#ifndef UI_STEAMPROFILESGUI_H
#define UI_STEAMPROFILESGUI_H

#include <QtCore/QVariant>
#include <QtGui/QAction>
#include <QtGui/QApplication>
#include <QtGui/QButtonGroup>
#include <QtGui/QHeaderView>
#include <QtGui/QLabel>
#include <QtGui/QMainWindow>
#include <QtGui/QMenu>
#include <QtGui/QMenuBar>
#include <QtGui/QStatusBar>
#include <QtGui/QWidget>

QT_BEGIN_NAMESPACE

class Ui_SteamProfilesGUI
{
public:
    QAction *actionExit;
    QAction *actionAbout;
    QWidget *centralWidget;
    QLabel *label;
    QLabel *label_2;
    QStatusBar *statusBar;
    QMenuBar *menuBar;
    QMenu *menuSteam_Profiles;

    void setupUi(QMainWindow *SteamProfilesGUI)
    {
        if (SteamProfilesGUI->objectName().isEmpty())
            SteamProfilesGUI->setObjectName(QString::fromUtf8("SteamProfilesGUI"));
        SteamProfilesGUI->resize(400, 300);
        SteamProfilesGUI->setMinimumSize(QSize(400, 300));
        SteamProfilesGUI->setMaximumSize(QSize(400, 300));
        SteamProfilesGUI->setStyleSheet(QString::fromUtf8("QMainWindow{background-color:white;}"));
        actionExit = new QAction(SteamProfilesGUI);
        actionExit->setObjectName(QString::fromUtf8("actionExit"));
        actionAbout = new QAction(SteamProfilesGUI);
        actionAbout->setObjectName(QString::fromUtf8("actionAbout"));
        centralWidget = new QWidget(SteamProfilesGUI);
        centralWidget->setObjectName(QString::fromUtf8("centralWidget"));
        label = new QLabel(centralWidget);
        label->setObjectName(QString::fromUtf8("label"));
        label->setGeometry(QRect(-1, 10, 401, 25));
        label_2 = new QLabel(centralWidget);
        label_2->setObjectName(QString::fromUtf8("label_2"));
        label_2->setGeometry(QRect(30, 40, 68, 18));
        SteamProfilesGUI->setCentralWidget(centralWidget);
        statusBar = new QStatusBar(SteamProfilesGUI);
        statusBar->setObjectName(QString::fromUtf8("statusBar"));
        SteamProfilesGUI->setStatusBar(statusBar);
        menuBar = new QMenuBar(SteamProfilesGUI);
        menuBar->setObjectName(QString::fromUtf8("menuBar"));
        menuBar->setGeometry(QRect(0, 0, 400, 20));
        menuSteam_Profiles = new QMenu(menuBar);
        menuSteam_Profiles->setObjectName(QString::fromUtf8("menuSteam_Profiles"));
        SteamProfilesGUI->setMenuBar(menuBar);

        menuBar->addAction(menuSteam_Profiles->menuAction());
        menuSteam_Profiles->addAction(actionAbout);
        menuSteam_Profiles->addSeparator();
        menuSteam_Profiles->addAction(actionExit);

        retranslateUi(SteamProfilesGUI);

        QMetaObject::connectSlotsByName(SteamProfilesGUI);
    } // setupUi

    void retranslateUi(QMainWindow *SteamProfilesGUI)
    {
        SteamProfilesGUI->setWindowTitle(QApplication::translate("SteamProfilesGUI", "SteamProfilesGUI", 0, QApplication::UnicodeUTF8));
        actionExit->setText(QApplication::translate("SteamProfilesGUI", "Exit", 0, QApplication::UnicodeUTF8));
        actionAbout->setText(QApplication::translate("SteamProfilesGUI", "About", 0, QApplication::UnicodeUTF8));
        label->setText(QApplication::translate("SteamProfilesGUI", "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0//EN\" \"http://www.w3.org/TR/REC-html40/strict.dtd\">\n"
"<html><head><meta name=\"qrichtext\" content=\"1\" /><style type=\"text/css\">\n"
"p, li { white-space: pre-wrap; }\n"
"</style></head><body style=\" font-family:'MS Shell Dlg 2'; font-size:8.25pt; font-weight:400; font-style:normal;\">\n"
"<p align=\"center\" style=\" margin-top:0px; margin-bottom:0px; margin-left:0px; margin-right:0px; -qt-block-indent:0; text-indent:0px;\"><span style=\" font-family:'Verdana'; font-size:16pt; font-weight:600; color:#32124d;\">Steam Profiles</span></p></body></html>", 0, QApplication::UnicodeUTF8));
        label_2->setText(QApplication::translate("SteamProfilesGUI", "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0//EN\" \"http://www.w3.org/TR/REC-html40/strict.dtd\">\n"
"<html><head><meta name=\"qrichtext\" content=\"1\" /><style type=\"text/css\">\n"
"p, li { white-space: pre-wrap; }\n"
"</style></head><body style=\" font-family:'MS Shell Dlg 2'; font-size:8.25pt; font-weight:400; font-style:normal;\">\n"
"<p style=\" margin-top:0px; margin-bottom:0px; margin-left:0px; margin-right:0px; -qt-block-indent:0; text-indent:0px;\"><span style=\" font-family:'Verdana'; font-size:12pt; text-decoration: underline;\">Profiles:</span></p></body></html>", 0, QApplication::UnicodeUTF8));
        menuSteam_Profiles->setTitle(QApplication::translate("SteamProfilesGUI", "Steam Profiles", 0, QApplication::UnicodeUTF8));
    } // retranslateUi

};

namespace Ui {
    class SteamProfilesGUI: public Ui_SteamProfilesGUI {};
} // namespace Ui

QT_END_NAMESPACE

#endif // UI_STEAMPROFILESGUI_H
