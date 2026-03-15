package com.example.week4task.service;

import org.springframework.stereotype.Service;

@Service
public class SMSNotificationService implements NotificationService {

    @Override
    public String sendNotification() {
        return "SMS Notification Sent";
    }

}