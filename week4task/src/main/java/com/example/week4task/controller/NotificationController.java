package com.example.week4task.controller;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.beans.factory.annotation.Qualifier;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RestController;

import com.example.week4task.service.NotificationService;

@RestController
public class NotificationController {

    @Autowired
    @Qualifier("emailNotificationService")
    NotificationService service;

    @GetMapping("/notify")
    public String notifyUser() {
        return service.sendNotification();
    }

}