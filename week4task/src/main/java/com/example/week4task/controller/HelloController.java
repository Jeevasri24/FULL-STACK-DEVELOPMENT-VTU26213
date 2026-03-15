package com.example.week4task.controller;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RestController;

import com.example.week4task.service.HelloService;

@RestController
public class HelloController {

    @Autowired
    HelloService service;

    @GetMapping("/hello")
    public String show() {
        return service.message();
    }

}