package com.example.week4task.service;

import org.springframework.stereotype.Service;

@Service
public class HelloService {

    public String message() {
        return "Hello from Service Layer";
    }

}
