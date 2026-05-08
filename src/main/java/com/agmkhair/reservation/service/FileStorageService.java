package com.agmkhair.reservation.service;

import org.springframework.stereotype.Service;
import org.springframework.web.multipart.MultipartFile;

import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.nio.file.StandardCopyOption;
import java.util.UUID;

@Service
public class FileStorageService {

    // ফাইল যেখানে সেভ হবে (প্রজেক্টের বাইরে কোনো ফোল্ডার হলে ভালো)
    private final String uploadDir = "uploads/airlines/";

    public String storeFile(MultipartFile file) {
        try {
            if (file == null || file.isEmpty()) return null;

            // ফোল্ডার না থাকলে তৈরি করা
            Path uploadPath = Paths.get(uploadDir);
            if (!Files.exists(uploadPath)) {
                Files.createDirectories(uploadPath);
            }

            // ইউনিক ফাইল নাম তৈরি (collission এড়াতে)
            String fileName = UUID.randomUUID().toString() + "_" + file.getOriginalFilename();
            Path filePath = uploadPath.resolve(fileName);

            // ফাইল সেভ করা
            Files.copy(file.getInputStream(), filePath, StandardCopyOption.REPLACE_EXISTING);

            // ফাইলটির এক্সেস URL রিটার্ন করা (যেমন: /uploads/airlines/uuid_name.png)
            return "/api/files/" + fileName;
        } catch (IOException e) {
            throw new RuntimeException("Could not store file. Error: " + e.getMessage());
        }
    }


}
