package com.example.week4task.controller;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RestController;

import com.example.week4task.service.GreetingService;

@RestController
public class GreetingController {

    @Autowired
    GreetingService service;

    @GetMapping("/greet")
    public String greetUser() {
        return service.getMessage();
    }

}
