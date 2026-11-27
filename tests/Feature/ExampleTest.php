<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_homepage_shows_the_bengkel_syifa_landing_page(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Bengkel Syifa');
        $response->assertSee('Motor Anda,');
        $response->assertSee('Empat sistem diperiksa dalam satu pemindaian');
        $response->assertDontSee('Ruang kerja');
        $response->assertDontSee('Buka dashboard admin');
    }

    /**
     * Test validation fails when submitting empty form.
     */
    public function test_question_validation_fails_when_empty(): void
    {
        $response = $this->post('/question/store', []);

        $response->assertSessionHasErrors([
            'nama' => 'Nama tidak boleh kosong',
            'email' => 'Email tidak boleh kosong',
            'pertanyaan' => 'Pertanyaan tidak boleh kosong',
        ]);
    }

    /**
     * Test validation fails when submitting invalid data.
     */
    public function test_question_validation_fails_when_invalid(): void
    {
        $response = $this->post('/question/store', [
            'nama' => 'Zoro',
            'email' => 'zoro-bukan-email',
            'pertanyaan' => 'Pendek',
        ]);

        $response->assertSessionHasErrors([
            'nama' => 'Nama minimal 5 karakter',
            'email' => 'Email tidak valid',
            'pertanyaan' => 'Pertanyaan minimal 10 karakter',
        ]);
    }

    /**
     * Test successful submission and response view rendering.
     */
    public function test_question_submit_successful(): void
    {
        $response = $this->post('/question/store', [
            'nama' => 'Harun Al Rasyid',
            'email' => 'harun25ti@mahasiswa.pcr.ac.id',
            'pertanyaan' => 'Apakah PCR memiliki program studi AI yang terakreditasi?',
        ]);

        $response->assertStatus(200);
        $response->assertViewIs('home-question-respon');
        $response->assertSee('Harun Al Rasyid');
        $response->assertSee('harun25ti@mahasiswa.pcr.ac.id');
        $response->assertSee('Apakah PCR memiliki program studi AI yang terakreditasi?');
    }
}
