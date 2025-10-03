pipeline {
  agent any

  options {
    skipDefaultCheckout(true)
    disableConcurrentBuilds()
    timestamps()
  }

  parameters {
    string(
      name: 'DOCKERHUB_TRIGGER_URL',
      defaultValue: '',
      description: 'URL del Build Trigger de Docker Hub (se usa solo si NO hay Docker en el nodo).'
    )
  }

  environment {
    IMAGE_NAME      = 'miguel1212/php-simple-app'
    DOCKER_BUILDKIT = '1'
  }

  stages {
    stage('Checkout') {
      steps {
        git branch: 'main', url: 'https://github.com/miguevillamil1212/php-simple-app.git'
      }
    }

    stage('Detectar Docker en el nodo') {
      steps {
        script {
          def rc = sh(script: 'command -v docker >/dev/null 2>&1', returnStatus: true)
          env.HAS_DOCKER = (rc == 0) ? 'true' : 'false'
          echo "HAS_DOCKER = ${env.HAS_DOCKER}"
        }
      }
    }

    /* ========= CAMINO A: Docker local disponible ========= */
    stage('Build Docker Image (local)') {
      when { expression { env.HAS_DOCKER == 'true' } }
      steps {
        sh '''
          set -euxo pipefail
          docker version
          docker build \
            -t $IMAGE_NAME:latest \
            -t $IMAGE_NAME:${BUILD_NUMBER} .
        '''
      }
    }

    stage('Login & Push (local)') {
      when { expression { env.HAS_DOCKER == 'true' } }
      steps {
        withCredentials([usernamePassword(
          credentialsId: 'docker-hub-creds',   // <- tu credencial de Docker Hub
          usernameVariable: 'DOCKERHUB_USER',
          passwordVariable: 'DOCKERHUB_PASS'
        )]) {
          sh '''
            set -euxo pipefail
            echo "$DOCKERHUB_PASS" | docker login -u "$DOCKERHUB_USER" --password-stdin
            docker push $IMAGE_NAME:latest
            docker push $IMAGE_NAME:${BUILD_NUMBER}
          '''
        }
      }
      post {
        always {
          sh 'docker logout || true'
        }
      }
    }

    stage('Cleanup (local)') {
      when { expression { env.HAS_DOCKER == 'true' } }
      steps {
        sh 'docker system prune -f || true'
      }
    }

    /* ========= CAMINO B: Sin Docker → build remoto en Docker Hub ========= */
    stage('Trigger Docker Hub build (remoto)') {
      when { expression { env.HAS_DOCKER == 'false' } }
      steps {
        script {
          def triggerUrl = (params.DOCKERHUB_TRIGGER_URL ?: '').trim()
          if (!triggerUrl) {
            error('No hay Docker en el nodo y DOCKERHUB_TRIGGER_URL está vacío. Proporciona el Trigger URL de Docker Hub al lanzar el job.')
          }
          sh """
            set -euxo pipefail
            echo 'Disparando build remoto en Docker Hub...'
            curl -fsSL -X POST -H 'Content-Type: application/json' -d '{"build": true}' '${triggerUrl}'
            echo 'Trigger enviado. El build/push se ejecutará en Docker Hub.'
          """
        }
      }
    }
  }

  post {
    success  { echo '✅ Pipeline completado con éxito' }
    failure  { echo '❌ Pipeline falló (revisa si pegaste DOCKERHUB_TRIGGER_URL o instala Docker en el nodo)' }
  }
}
