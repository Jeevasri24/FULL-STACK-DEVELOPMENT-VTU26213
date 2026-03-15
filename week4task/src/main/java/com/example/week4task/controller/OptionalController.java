package com.example.week4task.controller;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RestController;

import com.example.week4task.service.OptionalComponent;

@RestController
public class OptionalController {

    @Autowired(required = false)
    OptionalComponent component;

    @GetMapping("/optional")
    public String checkOptional() {

        if(component != null) {
            return component.showMessage();
        }

        return "Optional Component Not Found";
    }

}