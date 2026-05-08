package com.agmkhair.reservation.controller;

import com.agmkhair.reservation.entry.Users;
import com.agmkhair.reservation.repository.UserRepository;
import lombok.Data;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

import java.util.Optional;

@RestController
@RequestMapping("/api/auth")
@CrossOrigin("*")
public class AuthController {

    @Autowired
    private UserRepository userRepository;

    @PostMapping("/login")
    public ResponseEntity<?> login(@RequestBody LoginRequest request) {

        Optional<Users> user =
                userRepository.findByEmail(request.getEmail());

        if (user.isEmpty()) {
            return ResponseEntity.badRequest().body("User not found");
        }

        if (!user.get().getPassword().equals(request.getPassword())) {
            return ResponseEntity.badRequest().body("Wrong password");
        }

        return ResponseEntity.ok(user.get());
    }

    @Data
    public static class LoginRequest {
        private String email;
        private String password;
    }
}