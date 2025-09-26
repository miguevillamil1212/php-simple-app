pipeline {
    agent {
        docker {
            image 'docker:latest'
            args '-v /var/run/docker.sock:/var/run/docker.sock -v /tmp:/tmp'
        }
    }
    
    environment {
        // Credenciales de Docker Hub
        DOCKER_CREDS = credentials('docker-hub-creds')
        
        // Información de la imagen
        DOCKER_IMAGE = 'jamescanos/php-simple-app'
        DOCKER_REGISTRY = 'https://registry.hub.docker.com'
        
        // Versión basada en el build number
        VERSION = "${env.BUILD_ID}"
    }
    
    stages {
        stage('Verify Structure') {
            steps {
                sh '''
                    echo "=== Estructura del proyecto ==="
                    ls -la
                    echo "=== Contenido de php-simple-app/src/ ==="
                    ls -la php-simple-app/src/
                    echo "=== Dockerfile content ==="
                    cat Dockerfile
                '''
            }
        }
        
        stage('Build Docker Image') {
            steps {
                echo 'Construyendo imagen Docker...'
                script {
                    // Verificar estructura antes de construir
                    sh '''
                        echo "=== Preparando archivos para Docker ==="
                        # Copiar los archivos PHP al directorio actual para el build
                        cp -r php-simple-app/src/* ./
                        ls -la
                    '''
                    
                    docker.build("${DOCKER_IMAGE}:${VERSION}")
                }
            }
        }
        
        stage('Test Image') {
            steps {
                echo 'Probando imagen Docker...'
                script {
                    def testContainer = docker.image("${DOCKER_IMAGE}:${VERSION}")
                    testContainer.inside {
                        sh '''
                            echo "=== Verificando PHP ==="
                            php --version
                            echo "=== Verificando Apache ==="
                            apache2 -v
                            echo "=== Verificando archivos de la aplicación ==="
                            ls -la /var/www/html/
                            echo "=== Verificando que index.php existe ==="
                            cat /var/www/html/index.php || echo "index.php no encontrado"
                        '''
                    }
                }
            }
        }
        
        stage('Push to Docker Hub') {
            steps {
                echo 'Subiendo imagen a Docker Hub...'
                script {
                    docker.withRegistry("${DOCKER_REGISTRY}", 'docker-hub-creds') {
                        // Subir versión específica
                        docker.image("${DOCKER_IMAGE}:${VERSION}").push()
                        
                        // También subir como latest
                        docker.image("${DOCKER_IMAGE}:${VERSION}").push('latest')
                    }
                }
            }
        }
        
        stage('Deploy to Test') {
            steps {
                echo 'Desplegando aplicación de prueba...'
                script {
                    sh """
                    # Detener contenedor anterior si existe
                    docker stop test-php-app || true
                    docker rm test-php-app || true
                    
                    # Ejecutar nuevo contenedor
                    docker run -d \\
                        -p 8082:80 \\
                        --name test-php-app \\
                        ${DOCKER_IMAGE}:${VERSION}
                    """
                    
                    // Esperar que la aplicación esté lista
                    sleep 20
                    
                    // Probar que la aplicación responde
                    sh '''
                        echo "=== Probando aplicación en http://localhost:8082/ ==="
                        curl -f http://localhost:8082/ 
                        echo "✅ Aplicación funciona correctamente"
                    '''
                }
            }
        }
    }
    
    post {
        always {
            echo '=== Limpieza final ==='
            sh '''
                docker stop test-php-app || true 
                docker rm test-php-app || true
                echo "Contenedores limpiados"
            '''
            cleanWs()
        }
        success {
            echo '🎉 ¡Pipeline ejecutado exitosamente!'
            echo "📦 Imagen Docker: ${DOCKER_IMAGE}:${VERSION}"
            echo "🐳 Disponible en Docker Hub: https://hub.docker.com/r/jamescanos/php-simple-app"
        }
        failure {
            echo '❌ Pipeline falló'
        }
    }
}