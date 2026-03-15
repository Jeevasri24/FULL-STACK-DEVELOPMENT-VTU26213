package com.example.week4task.service;

import org.springframework.stereotype.Service;

@Service
public class PaymentServiceImpl implements PaymentService {

    public String processPayment() {
        return "Payment Successful";
    }

}