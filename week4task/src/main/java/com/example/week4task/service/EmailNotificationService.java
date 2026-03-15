package com.example.week4task.service;

import org.springframework.stereotype.Service;

@Service
public class EmailNotificationService implements NotificationService {

    @Override
    public String sendNotification() {
        return "Email Notification Sent";
    }

}